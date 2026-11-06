-- ACCN ecommerce v2 MVP. Migracion incremental desde v1; MariaDB 10.4.32.
-- Respaldo previo y detener escrituras mientras se modifica el esquema.
-- Preserva catalogo, pedidos, sesiones, pagos y eventos existentes.
-- Aborta si hay inventario, devoluciones o varias cuentas por proveedor/ambiente.
-- DDL realiza commits implicitos. No usar FOREIGN_KEY_CHECKS=0 ni reintentar
-- automaticamente ante un fallo parcial. No contiene credenciales.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET time_zone = '+00:00';
SET SESSION sql_mode = CONCAT(@@session.sql_mode, ',STRICT_ALL_TABLES');

DELIMITER $$
CREATE PROCEDURE accn_simplify_ecommerce_20261106()
SQL SECURITY INVOKER
BEGIN
    DECLARE acquired INT DEFAULT 0;
    DECLARE lock_name VARCHAR(64);
    DECLARE table_count INT DEFAULT 0;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        IF acquired = 1 THEN
            DO RELEASE_LOCK(lock_name);
        END IF;
        RESIGNAL;
    END;

    IF DATABASE() IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Seleccione la base de datos destino.';
    END IF;
    SET lock_name = CONCAT('accn:ecommerce:', LEFT(SHA2(DATABASE(), 256), 40));
    SET acquired = GET_LOCK(lock_name, 5);
    IF acquired IS NULL OR acquired <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Otra migracion ecommerce esta en curso.';
    END IF;

    IF EXISTS (SELECT 1 FROM ecommerce_schema_versions WHERE version = '2026-11-06-v2-mvp') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El esquema MVP ya esta instalado; no se permite repetir.';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM ecommerce_schema_versions WHERE version = '2026-11-06-v1') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Se requiere el esquema ecommerce v1.';
    END IF;
    SELECT COUNT(*) INTO table_count FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' AND TABLE_NAME IN (
        'products','products_meta','product_variants','orders','orders_product',
        'inventory_reservations','inventory_movements','payment_accounts',
        'payment_sessions','payments','payment_events','payment_refunds','ecommerce_schema_versions'
    );
    IF table_count <> 13 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El esquema v1 esta incompleto; revisar antes de migrar.';
    END IF;
    IF EXISTS (SELECT 1 FROM inventory_reservations LIMIT 1)
       OR EXISTS (SELECT 1 FROM inventory_movements LIMIT 1)
       OR EXISTS (SELECT 1 FROM product_variants WHERE stock_on_hand <> 0 OR stock_reserved <> 0 LIMIT 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Existe inventario que debe conservarse antes de simplificar.';
    END IF;
    IF EXISTS (SELECT 1 FROM payment_refunds LIMIT 1)
       OR EXISTS (SELECT 1 FROM payments WHERE refunded_amount <> 0 LIMIT 1)
       OR EXISTS (SELECT 1 FROM orders WHERE refunded_total <> 0 LIMIT 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Existen devoluciones que requieren migracion de historial.';
    END IF;
    IF EXISTS (
        SELECT provider, environment FROM payment_accounts
        GROUP BY provider, environment HAVING COUNT(*) > 1
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hay varias cuentas para un proveedor/ambiente; conservar su identidad.';
    END IF;
    IF EXISTS (
        SELECT 1 FROM information_schema.REFERENTIAL_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND REFERENCED_TABLE_NAME IN (
              'inventory_reservations','inventory_movements','payment_accounts',
              'payment_refunds','payment_sessions','payments'
          )
          AND CONSTRAINT_NAME NOT IN (
              'fk_inventory_movement_reservation','fk_payment_sessions_account',
              'fk_payments_session','fk_payment_events_account',
              'fk_payment_events_payment','fk_payment_refunds_payment'
          )
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Existen dependencias adicionales; revisar antes de simplificar.';
    END IF;

    ALTER TABLE payment_sessions
        ADD COLUMN provider VARCHAR(40) COLLATE utf8mb4_bin NULL AFTER order_id,
        ADD COLUMN environment VARCHAR(12) COLLATE utf8mb4_bin NULL AFTER provider;
    ALTER TABLE payments
        ADD COLUMN provider VARCHAR(40) COLLATE utf8mb4_bin NULL AFTER payment_session_id,
        ADD COLUMN environment VARCHAR(12) COLLATE utf8mb4_bin NULL AFTER provider,
        ADD COLUMN refund_reference VARCHAR(255) COLLATE utf8mb4_bin NULL AFTER refunded_amount,
        ADD COLUMN refunded_at DATETIME(6) NULL AFTER refund_reference,
        ADD COLUMN refund_reason VARCHAR(500) NULL AFTER refunded_at;
    ALTER TABLE payment_events
        ADD COLUMN provider VARCHAR(40) COLLATE utf8mb4_bin NULL AFTER id,
        ADD COLUMN environment VARCHAR(12) COLLATE utf8mb4_bin NULL AFTER provider;

    -- Preservar identidad historica y updated_at durante el cambio de columnas.
    UPDATE payment_sessions s INNER JOIN payment_accounts a ON a.id = s.payment_account_id
    SET s.provider = a.provider, s.environment = LOWER(a.environment), s.updated_at = s.updated_at;
    UPDATE payments p INNER JOIN payment_accounts a ON a.id = p.payment_account_id
    SET p.provider = a.provider, p.environment = LOWER(a.environment), p.updated_at = p.updated_at;
    UPDATE payment_events e INNER JOIN payment_accounts a ON a.id = e.payment_account_id
    SET e.provider = a.provider, e.environment = LOWER(a.environment);

    -- Solo se eliminan tablas cuyo contenido fue comprobado como vacio.
    DROP TABLE inventory_movements;
    DROP TABLE inventory_reservations;
    DROP TABLE payment_refunds;

    ALTER TABLE payment_events
        DROP FOREIGN KEY fk_payment_events_account,
        DROP FOREIGN KEY fk_payment_events_payment;
    ALTER TABLE payments DROP FOREIGN KEY fk_payments_session;
    ALTER TABLE payment_sessions DROP FOREIGN KEY fk_payment_sessions_account;

    ALTER TABLE payment_sessions
        DROP INDEX uq_payment_sessions_reference,
        DROP INDEX uq_payment_sessions_request,
        DROP INDEX uq_payment_sessions_scope,
        DROP COLUMN payment_account_id,
        MODIFY COLUMN provider VARCHAR(40) COLLATE utf8mb4_bin NOT NULL,
        MODIFY COLUMN environment VARCHAR(12) COLLATE utf8mb4_bin NOT NULL,
        ADD UNIQUE KEY uq_payment_sessions_reference (provider, environment, merchant_reference),
        ADD UNIQUE KEY uq_payment_sessions_request (provider, environment, idempotency_key),
        ADD UNIQUE KEY uq_payment_sessions_scope (id, provider, environment, currency),
        ADD CONSTRAINT ck_payment_sessions_provider CHECK (CHAR_LENGTH(TRIM(provider)) > 0),
        ADD CONSTRAINT ck_payment_sessions_environment CHECK (environment IN ('sandbox','production'));

    ALTER TABLE payments
        DROP INDEX uq_payments_provider_id,
        DROP INDEX uq_payments_request,
        DROP INDEX uq_payments_scope,
        DROP INDEX uq_payments_account,
        DROP INDEX idx_payments_session_scope,
        DROP COLUMN payment_account_id,
        MODIFY COLUMN provider VARCHAR(40) COLLATE utf8mb4_bin NOT NULL,
        MODIFY COLUMN environment VARCHAR(12) COLLATE utf8mb4_bin NOT NULL,
        ADD UNIQUE KEY uq_payments_provider_id (provider, environment, provider_payment_id),
        ADD UNIQUE KEY uq_payments_request (provider, environment, idempotency_key),
        ADD UNIQUE KEY uq_payments_provider_scope (id, provider, environment),
        ADD KEY idx_payments_session_scope (payment_session_id, provider, environment, currency),
        ADD CONSTRAINT fk_payments_session FOREIGN KEY (payment_session_id, provider, environment, currency)
            REFERENCES payment_sessions (id, provider, environment, currency) ON DELETE RESTRICT,
        ADD CONSTRAINT ck_payments_provider CHECK (CHAR_LENGTH(TRIM(provider)) > 0),
        ADD CONSTRAINT ck_payments_environment CHECK (environment IN ('sandbox','production')),
        ADD CONSTRAINT ck_payments_manual_refund CHECK (
            (refunded_amount = 0 AND refund_reference IS NULL AND refunded_at IS NULL AND refund_reason IS NULL)
            OR (refunded_amount > 0 AND refund_reference IS NOT NULL
                AND CHAR_LENGTH(TRIM(refund_reference)) > 0 AND refunded_at IS NOT NULL)
        );

    ALTER TABLE payment_events
        DROP INDEX uq_payment_events_deduplication,
        DROP INDEX idx_payment_events_reference,
        DROP INDEX idx_payment_events_payment,
        DROP COLUMN payment_account_id,
        MODIFY COLUMN provider VARCHAR(40) COLLATE utf8mb4_bin NOT NULL,
        MODIFY COLUMN environment VARCHAR(12) COLLATE utf8mb4_bin NOT NULL,
        ADD UNIQUE KEY uq_payment_events_deduplication (provider, environment, deduplication_key),
        ADD KEY idx_payment_events_reference (provider, environment, merchant_reference),
        ADD KEY idx_payment_events_payment (payment_id, provider, environment),
        ADD CONSTRAINT fk_payment_events_payment FOREIGN KEY (payment_id, provider, environment)
            REFERENCES payments (id, provider, environment) ON DELETE RESTRICT,
        ADD CONSTRAINT ck_payment_events_provider CHECK (CHAR_LENGTH(TRIM(provider)) > 0),
        ADD CONSTRAINT ck_payment_events_environment CHECK (environment IN ('sandbox','production'));

    DROP TABLE payment_accounts;

    ALTER TABLE product_variants
        DROP CONSTRAINT ck_product_variants_flags,
        DROP CONSTRAINT ck_product_variants_stock,
        DROP COLUMN track_inventory,
        DROP COLUMN stock_on_hand,
        DROP COLUMN stock_reserved,
        ADD CONSTRAINT ck_product_variants_active CHECK (is_active IN (0,1));
    ALTER TABLE products
        DROP CONSTRAINT ck_products_kind,
        MODIFY COLUMN product_kind VARCHAR(16) NOT NULL DEFAULT 'digital',
        ADD CONSTRAINT ck_products_kind CHECK (product_kind IN ('physical','digital','service','access'));

    INSERT INTO ecommerce_schema_versions (version) VALUES ('2026-11-06-v2-mvp');
    DO RELEASE_LOCK(lock_name);
END$$
DELIMITER ;

CALL accn_simplify_ecommerce_20261106();
DROP PROCEDURE accn_simplify_ecommerce_20261106;
