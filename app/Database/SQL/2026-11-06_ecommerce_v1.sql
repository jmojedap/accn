-- ACCN ecommerce v1. MariaDB 10.4.32; MySQL 8 requiere validacion propia.
-- Seleccionar primero la base destino. Hacer respaldo y detener escrituras.
-- RECONSTRUCCION: elimina los productos y metadatos importados.
-- Requiere las cuatro tablas heredadas y pedidos completamente vacios.
-- No desactiva FOREIGN_KEY_CHECKS. DDL no se revierte con ROLLBACK.
-- El procedimiento aborta si el esquema nuevo ya existe.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET time_zone = '+00:00';

DELIMITER $$
CREATE PROCEDURE accn_install_ecommerce_20261106()
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
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Seleccione una base de datos destino.';
    END IF;
    SET lock_name = CONCAT('accn:ecommerce:', LEFT(SHA2(DATABASE(), 256), 40));
    SET acquired = GET_LOCK(lock_name, 5);
    IF acquired IS NULL OR acquired <> 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Otra instalacion de ecommerce esta en curso.';
    END IF;

    SELECT COUNT(*) INTO table_count FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (
        'ecommerce_schema_versions', 'product_variants', 'inventory_reservations',
        'inventory_movements', 'payment_accounts', 'payment_sessions', 'payments',
        'payment_events', 'payment_refunds'
    );
    IF table_count <> 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Esquema ecommerce nuevo existente: no se permite reinstalar.';
    END IF;

    SELECT COUNT(*) INTO table_count FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'
      AND TABLE_NAME IN ('orders', 'orders_product', 'products', 'products_meta');
    IF table_count <> 4 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Se requieren las cuatro tablas heredadas originales.';
    END IF;
    IF EXISTS (SELECT 1 FROM orders LIMIT 1)
       OR EXISTS (SELECT 1 FROM orders_product LIMIT 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Existen pedidos o lineas: se requiere migracion de datos.';
    END IF;
    IF EXISTS (
        SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
          AND REFERENCED_TABLE_NAME IN ('orders', 'orders_product', 'products', 'products_meta')
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Existen FK hacia tablas heredadas: revisar dependencias.';
    END IF;

    -- DROP TABLE elimina tambien el trigger roto after_delete de orders.
    DROP TABLE orders_product;
    DROP TABLE products_meta;
    DROP TABLE orders;
    DROP TABLE products;

    CREATE TABLE products (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        idcode VARCHAR(80) COLLATE utf8mb4_bin NOT NULL,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(180) NOT NULL,
        status TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 borrador, 1 activo, 2 archivado',
        type_id SMALLINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Clasificacion propia del proyecto',
        product_kind VARCHAR(16) NOT NULL DEFAULT 'physical',
        description MEDIUMTEXT NULL,
        meta_description VARCHAR(512) NULL,
        currency CHAR(3) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'COP',
        cost DECIMAL(18,2) NOT NULL DEFAULT 0,
        base_price DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT 'Precio unitario antes de impuestos',
        tax_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
        weight_grams DECIMAL(12,3) NOT NULL DEFAULT 0,
        width_cm DECIMAL(10,2) NOT NULL DEFAULT 0,
        height_cm DECIMAL(10,2) NOT NULL DEFAULT 0,
        depth_cm DECIMAL(10,2) NOT NULL DEFAULT 0,
        image_url VARCHAR(500) NULL,
        creator_id INT NULL,
        updater_id INT NULL,
        created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
        deleted_at DATETIME(6) NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_products_idcode (idcode),
        UNIQUE KEY uq_products_slug (slug),
        KEY idx_products_catalog (status, type_id, deleted_at),
        CONSTRAINT ck_products_identifiers CHECK (CHAR_LENGTH(TRIM(idcode)) > 0 AND CHAR_LENGTH(TRIM(slug)) > 0 AND CHAR_LENGTH(TRIM(name)) > 0),
        CONSTRAINT ck_products_status CHECK (status IN (0, 1, 2)),
        CONSTRAINT ck_products_kind CHECK (product_kind IN ('physical', 'digital', 'service')),
        CONSTRAINT ck_products_currency CHECK (currency REGEXP '^[A-Z]{3}$'),
        CONSTRAINT ck_products_amounts CHECK (cost >= 0 AND base_price >= 0 AND tax_percent BETWEEN 0 AND 100),
        CONSTRAINT ck_products_dimensions CHECK (weight_grams >= 0 AND width_cm >= 0 AND height_cm >= 0 AND depth_cm >= 0)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE products_meta (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        product_id INT UNSIGNED NOT NULL,
        meta_key VARCHAR(100) COLLATE utf8mb4_bin NOT NULL,
        position SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 para valor unico; varias posiciones para listas',
        value_json JSON NOT NULL,
        creator_id INT NULL,
        updater_id INT NULL,
        created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
        PRIMARY KEY (id),
        UNIQUE KEY uq_products_meta_position (product_id, meta_key, position),
        CONSTRAINT fk_products_meta_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
        CONSTRAINT ck_products_meta_key CHECK (CHAR_LENGTH(TRIM(meta_key)) > 0)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE product_variants (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        product_id INT UNSIGNED NOT NULL,
        sku VARCHAR(100) COLLATE utf8mb4_bin NOT NULL,
        name VARCHAR(150) NOT NULL DEFAULT 'Default',
        attributes_json JSON NULL,
        base_price DECIMAL(18,2) NULL COMMENT 'NULL hereda products.base_price',
        cost DECIMAL(18,2) NULL COMMENT 'NULL hereda products.cost',
        is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
        track_inventory TINYINT UNSIGNED NOT NULL DEFAULT 1,
        stock_on_hand DECIMAL(14,3) NOT NULL DEFAULT 0,
        stock_reserved DECIMAL(14,3) NOT NULL DEFAULT 0,
        created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
        deleted_at DATETIME(6) NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_product_variants_sku (sku),
        UNIQUE KEY uq_product_variants_product (id, product_id),
        KEY idx_product_variants_active (product_id, is_active),
        CONSTRAINT fk_product_variants_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT,
        CONSTRAINT ck_product_variants_sku CHECK (CHAR_LENGTH(TRIM(sku)) > 0),
        CONSTRAINT ck_product_variants_price CHECK ((base_price IS NULL OR base_price >= 0) AND (cost IS NULL OR cost >= 0)),
        CONSTRAINT ck_product_variants_flags CHECK (is_active IN (0,1) AND track_inventory IN (0,1)),
        CONSTRAINT ck_product_variants_stock CHECK (stock_on_hand >= 0 AND stock_reserved >= 0 AND stock_reserved <= stock_on_hand)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE orders (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_code VARCHAR(64) COLLATE utf8mb4_bin NOT NULL,
        order_status VARCHAR(24) NOT NULL DEFAULT 'draft',
        payment_status VARCHAR(24) NOT NULL DEFAULT 'unpaid',
        shipping_status VARCHAR(24) NOT NULL DEFAULT 'not_required',
        user_id INT NULL COMMENT 'Usuario opcional; no FK hacia users MyISAM',
        buyer_name VARCHAR(150) NOT NULL,
        email VARCHAR(254) NOT NULL,
        document_type VARCHAR(20) NULL,
        document_number VARCHAR(50) NULL,
        phone_number VARCHAR(50) NULL,
        billing_address JSON NULL,
        shipping_address JSON NULL,
        currency CHAR(3) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'COP',
        subtotal DECIMAL(18,2) NOT NULL DEFAULT 0,
        discount_total DECIMAL(18,2) NOT NULL DEFAULT 0,
        tax_total DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT 'Incluye impuesto de productos y envio',
        shipping_total DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT 'Envio antes de impuestos',
        total_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
        paid_total DECIMAL(18,2) NOT NULL DEFAULT 0 COMMENT 'Cobros capturados; admite sobrepago',
        refunded_total DECIMAL(18,2) NOT NULL DEFAULT 0,
        shipping_method VARCHAR(80) NULL,
        shipping_code VARCHAR(100) NULL,
        invoice_reference VARCHAR(100) NULL,
        notes TEXT NULL,
        notes_admin TEXT NULL,
        metadata JSON NULL,
        confirmed_at DATETIME(6) NULL,
        cancelled_at DATETIME(6) NULL,
        creator_id INT NULL,
        updater_id INT NULL,
        created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
        PRIMARY KEY (id),
        UNIQUE KEY uq_orders_code (order_code),
        UNIQUE KEY uq_orders_currency (id, currency),
        KEY idx_orders_user_created (user_id, created_at),
        KEY idx_orders_status_created (order_status, created_at),
        KEY idx_orders_payment_created (payment_status, created_at),
        CONSTRAINT ck_orders_identity CHECK (CHAR_LENGTH(TRIM(order_code)) > 0 AND CHAR_LENGTH(TRIM(buyer_name)) > 0 AND CHAR_LENGTH(TRIM(email)) > 0),
        CONSTRAINT ck_orders_status CHECK (order_status IN ('draft','pending','confirmed','processing','completed','cancelled')),
        CONSTRAINT ck_orders_payment CHECK (payment_status IN ('unpaid','pending','authorized','partially_paid','paid','partially_refunded','refunded','disputed','charged_back','overpaid')),
        CONSTRAINT ck_orders_shipping CHECK (shipping_status IN ('not_required','unfulfilled','processing','shipped','delivered','returned','cancelled')),
        CONSTRAINT ck_orders_currency CHECK (currency REGEXP '^[A-Z]{3}$'),
        CONSTRAINT ck_orders_totals CHECK (subtotal >= 0 AND discount_total >= 0 AND discount_total <= subtotal AND tax_total >= 0 AND shipping_total >= 0 AND total_amount = subtotal - discount_total + tax_total + shipping_total),
        CONSTRAINT ck_orders_paid CHECK (paid_total >= 0 AND refunded_total >= 0 AND refunded_total <= paid_total)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE orders_product (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NOT NULL,
        variant_id INT UNSIGNED NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        sku VARCHAR(100) COLLATE utf8mb4_bin NOT NULL,
        variant_name VARCHAR(150) NULL,
        attributes_json JSON NULL COMMENT 'Copia historica de los atributos comprados',
        quantity DECIMAL(12,3) NOT NULL,
        unit_cost DECIMAL(18,2) NOT NULL DEFAULT 0,
        unit_price DECIMAL(18,2) NOT NULL COMMENT 'Precio antes de impuestos',
        subtotal DECIMAL(18,2) NOT NULL,
        discount_total DECIMAL(18,2) NOT NULL DEFAULT 0,
        tax_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
        tax_total DECIMAL(18,2) NOT NULL DEFAULT 0,
        line_total DECIMAL(18,2) NOT NULL,
        created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        PRIMARY KEY (id),
        UNIQUE KEY uq_orders_product_variant (id, variant_id),
        KEY idx_orders_product_order (order_id),
        KEY idx_orders_product_variant_product (variant_id, product_id),
        CONSTRAINT fk_orders_product_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE RESTRICT,
        CONSTRAINT fk_orders_product_variant FOREIGN KEY (variant_id, product_id) REFERENCES product_variants (id, product_id) ON DELETE RESTRICT,
        CONSTRAINT ck_orders_product_identity CHECK (CHAR_LENGTH(TRIM(product_name)) > 0 AND CHAR_LENGTH(TRIM(sku)) > 0),
        CONSTRAINT ck_orders_product_quantity CHECK (quantity > 0),
        CONSTRAINT ck_orders_product_totals CHECK (unit_cost >= 0 AND unit_price >= 0 AND subtotal = ROUND(quantity * unit_price, 2) AND discount_total >= 0 AND discount_total <= subtotal AND tax_percent BETWEEN 0 AND 100 AND tax_total >= 0 AND line_total = subtotal - discount_total + tax_total)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE inventory_reservations (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_product_id INT UNSIGNED NOT NULL,
        variant_id INT UNSIGNED NOT NULL,
        reservation_key VARCHAR(128) COLLATE utf8mb4_bin NOT NULL,
        quantity DECIMAL(14,3) NOT NULL,
        status VARCHAR(16) NOT NULL DEFAULT 'active',
        expires_at DATETIME(6) NOT NULL,
        closed_at DATETIME(6) NULL,
        created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        PRIMARY KEY (id),
        UNIQUE KEY uq_inventory_reservation_key (reservation_key),
        UNIQUE KEY uq_inventory_reservation_variant (id, variant_id),
        KEY idx_inventory_reservation_expiry (status, expires_at),
        CONSTRAINT fk_inventory_reservation_line FOREIGN KEY (order_product_id, variant_id) REFERENCES orders_product (id, variant_id) ON DELETE RESTRICT,
        CONSTRAINT ck_inventory_reservation_quantity CHECK (quantity > 0),
        CONSTRAINT ck_inventory_reservation_status CHECK (status IN ('active','consumed','released','expired')),
        CONSTRAINT ck_inventory_reservation_closed CHECK ((status = 'active' AND closed_at IS NULL) OR (status <> 'active' AND closed_at IS NOT NULL))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE inventory_movements (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        variant_id INT UNSIGNED NOT NULL,
        reservation_id BIGINT UNSIGNED NULL,
        operation_key VARCHAR(128) COLLATE utf8mb4_bin NOT NULL,
        movement_type VARCHAR(24) NOT NULL,
        quantity_delta DECIMAL(14,3) NOT NULL COMMENT 'Cambio firmado en stock_on_hand',
        reason VARCHAR(255) NULL,
        creator_id INT NULL,
        created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        PRIMARY KEY (id),
        UNIQUE KEY uq_inventory_movement_operation (operation_key),
        KEY idx_inventory_movement_variant_created (variant_id, created_at),
        CONSTRAINT fk_inventory_movement_variant FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE RESTRICT,
        CONSTRAINT fk_inventory_movement_reservation FOREIGN KEY (reservation_id, variant_id) REFERENCES inventory_reservations (id, variant_id) ON DELETE RESTRICT,
        CONSTRAINT ck_inventory_movement_quantity CHECK (quantity_delta <> 0),
        CONSTRAINT ck_inventory_movement_type CHECK (movement_type IN ('receipt','sale','return','adjustment')),
        CONSTRAINT ck_inventory_movement_direction CHECK ((movement_type = 'sale' AND quantity_delta < 0) OR (movement_type IN ('receipt','return') AND quantity_delta > 0) OR movement_type = 'adjustment')
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE payment_accounts (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        provider VARCHAR(40) COLLATE utf8mb4_bin NOT NULL COMMENT 'mercadopago, wompi, payu u otro adaptador',
        account_code VARCHAR(80) COLLATE utf8mb4_bin NOT NULL,
        environment VARCHAR(12) NOT NULL,
        merchant_account_id VARCHAR(255) COLLATE utf8mb4_bin NULL,
        credentials_key VARCHAR(150) NULL COMMENT 'Referencia a configuracion segura; nunca el secreto',
        is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
        settings_json JSON NULL COMMENT 'Solo configuracion no secreta',
        created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
        PRIMARY KEY (id),
        UNIQUE KEY uq_payment_accounts_scope (provider, account_code, environment),
        CONSTRAINT ck_payment_accounts_identity CHECK (CHAR_LENGTH(TRIM(provider)) > 0 AND CHAR_LENGTH(TRIM(account_code)) > 0),
        CONSTRAINT ck_payment_accounts_environment CHECK (environment IN ('sandbox','production')),
        CONSTRAINT ck_payment_accounts_active CHECK (is_active IN (0,1))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE payment_sessions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id INT UNSIGNED NOT NULL,
        payment_account_id INT UNSIGNED NOT NULL,
        merchant_reference VARCHAR(255) COLLATE utf8mb4_bin NOT NULL,
        idempotency_key VARCHAR(128) COLLATE utf8mb4_bin NOT NULL,
        provider_session_id VARCHAR(255) COLLATE utf8mb4_bin NULL,
        provider_order_id VARCHAR(255) COLLATE utf8mb4_bin NULL,
        status VARCHAR(24) NOT NULL DEFAULT 'created',
        amount DECIMAL(18,2) NOT NULL,
        currency CHAR(3) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
        checkout_url TEXT NULL,
        expires_at DATETIME(6) NULL,
        metadata JSON NULL,
        created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
        PRIMARY KEY (id),
        UNIQUE KEY uq_payment_sessions_reference (payment_account_id, merchant_reference),
        UNIQUE KEY uq_payment_sessions_request (payment_account_id, idempotency_key),
        UNIQUE KEY uq_payment_sessions_scope (id, payment_account_id, currency),
        KEY idx_payment_sessions_order_currency (order_id, currency),
        KEY idx_payment_sessions_expiry (status, expires_at),
        CONSTRAINT fk_payment_sessions_order FOREIGN KEY (order_id, currency) REFERENCES orders (id, currency) ON DELETE RESTRICT,
        CONSTRAINT fk_payment_sessions_account FOREIGN KEY (payment_account_id) REFERENCES payment_accounts (id) ON DELETE RESTRICT,
        CONSTRAINT ck_payment_sessions_keys CHECK (CHAR_LENGTH(TRIM(merchant_reference)) > 0 AND CHAR_LENGTH(TRIM(idempotency_key)) > 0),
        CONSTRAINT ck_payment_sessions_amount CHECK (amount > 0),
        CONSTRAINT ck_payment_sessions_status CHECK (status IN ('created','pending','completed','failed','expired','cancelled'))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE payments (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        payment_session_id BIGINT UNSIGNED NOT NULL,
        payment_account_id INT UNSIGNED NOT NULL,
        provider_payment_id VARCHAR(255) COLLATE utf8mb4_bin NULL,
        idempotency_key VARCHAR(128) COLLATE utf8mb4_bin NULL COMMENT 'NULL si el intento fue creado dentro del checkout externo',
        status VARCHAR(24) NOT NULL DEFAULT 'pending',
        provider_status VARCHAR(80) NULL,
        response_code VARCHAR(255) NULL,
        status_detail TEXT NULL,
        payment_method VARCHAR(80) NULL,
        installments SMALLINT UNSIGNED NULL,
        amount DECIMAL(18,2) NOT NULL,
        currency CHAR(3) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
        authorized_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
        captured_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
        refunded_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
        provider_created_at DATETIME(6) NULL,
        approved_at DATETIME(6) NULL,
        last_synced_at DATETIME(6) NULL,
        metadata JSON NULL,
        created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
        PRIMARY KEY (id),
        UNIQUE KEY uq_payments_provider_id (payment_account_id, provider_payment_id),
        UNIQUE KEY uq_payments_request (payment_account_id, idempotency_key),
        UNIQUE KEY uq_payments_scope (id, payment_account_id, currency),
        UNIQUE KEY uq_payments_account (id, payment_account_id),
        KEY idx_payments_session_scope (payment_session_id, payment_account_id, currency),
        KEY idx_payments_reconcile (status, last_synced_at),
        CONSTRAINT fk_payments_session FOREIGN KEY (payment_session_id, payment_account_id, currency) REFERENCES payment_sessions (id, payment_account_id, currency) ON DELETE RESTRICT,
        CONSTRAINT ck_payments_amounts CHECK (amount > 0 AND authorized_amount BETWEEN 0 AND amount AND captured_amount BETWEEN 0 AND amount AND refunded_amount BETWEEN 0 AND captured_amount),
        CONSTRAINT ck_payments_status CHECK (status IN ('pending','requires_action','authorized','approved','declined','cancelled','expired','error','partially_refunded','refunded','disputed','charged_back')),
        CONSTRAINT ck_payments_installments CHECK (installments IS NULL OR installments > 0),
        CONSTRAINT ck_payments_keys CHECK ((provider_payment_id IS NULL OR CHAR_LENGTH(TRIM(provider_payment_id)) > 0) AND (idempotency_key IS NULL OR CHAR_LENGTH(TRIM(idempotency_key)) > 0))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE payment_events (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        payment_account_id INT UNSIGNED NOT NULL,
        payment_id BIGINT UNSIGNED NULL,
        event_type VARCHAR(100) NOT NULL,
        provider_event_id VARCHAR(255) COLLATE utf8mb4_bin NULL,
        merchant_reference VARCHAR(255) COLLATE utf8mb4_bin NULL,
        deduplication_key VARCHAR(128) COLLATE utf8mb4_bin NOT NULL,
        payload JSON NOT NULL COMMENT 'Copia depurada, sin PAN, CVV, tokens ni secretos',
        headers_json JSON NULL COMMENT 'Solo cabeceras permitidas y depuradas',
        signature_valid TINYINT UNSIGNED NULL,
        processing_status VARCHAR(16) NOT NULL DEFAULT 'received',
        processing_attempts INT UNSIGNED NOT NULL DEFAULT 0,
        error_message TEXT NULL,
        event_at DATETIME(6) NULL,
        received_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        processed_at DATETIME(6) NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_payment_events_deduplication (payment_account_id, deduplication_key),
        KEY idx_payment_events_queue (processing_status, received_at),
        KEY idx_payment_events_reference (payment_account_id, merchant_reference),
        KEY idx_payment_events_payment (payment_id, payment_account_id),
        CONSTRAINT fk_payment_events_account FOREIGN KEY (payment_account_id) REFERENCES payment_accounts (id) ON DELETE RESTRICT,
        CONSTRAINT fk_payment_events_payment FOREIGN KEY (payment_id, payment_account_id) REFERENCES payments (id, payment_account_id) ON DELETE RESTRICT,
        CONSTRAINT ck_payment_events_keys CHECK (CHAR_LENGTH(TRIM(deduplication_key)) > 0 AND CHAR_LENGTH(TRIM(event_type)) > 0),
        CONSTRAINT ck_payment_events_signature CHECK (signature_valid IS NULL OR signature_valid IN (0,1)),
        CONSTRAINT ck_payment_events_status CHECK (processing_status IN ('received','processing','processed','ignored','failed','invalid')),
        CONSTRAINT ck_payment_events_verified CHECK (processing_status <> 'processed' OR (signature_valid IS NOT NULL AND signature_valid = 1 AND processed_at IS NOT NULL))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE payment_refunds (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        payment_id BIGINT UNSIGNED NOT NULL,
        payment_account_id INT UNSIGNED NOT NULL,
        provider_refund_id VARCHAR(255) COLLATE utf8mb4_bin NULL,
        idempotency_key VARCHAR(128) COLLATE utf8mb4_bin NOT NULL,
        amount DECIMAL(18,2) NOT NULL,
        currency CHAR(3) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
        status VARCHAR(24) NOT NULL DEFAULT 'pending',
        provider_status VARCHAR(80) NULL,
        reason VARCHAR(500) NULL,
        metadata JSON NULL,
        requested_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        completed_at DATETIME(6) NULL,
        updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
        PRIMARY KEY (id),
        UNIQUE KEY uq_payment_refunds_request (payment_account_id, idempotency_key),
        UNIQUE KEY uq_payment_refunds_provider (payment_account_id, provider_refund_id),
        KEY idx_payment_refunds_scope (payment_id, payment_account_id, currency),
        KEY idx_payment_refunds_status (status, requested_at),
        CONSTRAINT fk_payment_refunds_payment FOREIGN KEY (payment_id, payment_account_id, currency) REFERENCES payments (id, payment_account_id, currency) ON DELETE RESTRICT,
        CONSTRAINT ck_payment_refunds_amount CHECK (amount > 0),
        CONSTRAINT ck_payment_refunds_key CHECK (CHAR_LENGTH(TRIM(idempotency_key)) > 0),
        CONSTRAINT ck_payment_refunds_status CHECK (status IN ('pending','processing','approved','declined','cancelled','error'))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE ecommerce_schema_versions (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        version VARCHAR(40) NOT NULL,
        installed_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
        PRIMARY KEY (id),
        UNIQUE KEY uq_ecommerce_schema_version (version)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    INSERT INTO ecommerce_schema_versions (version) VALUES ('2026-11-06-v1');

    DO RELEASE_LOCK(lock_name);
END$$
DELIMITER ;

CALL accn_install_ecommerce_20261106();
DROP PROCEDURE accn_install_ecommerce_20261106;
