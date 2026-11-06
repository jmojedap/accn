-- Solo lectura. Seleccionar la base instalada antes de ejecutar.
SELECT DATABASE() AS database_name, VERSION() AS server_version;
SELECT version, installed_at FROM ecommerce_schema_versions;

SELECT TABLE_NAME, ENGINE, TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (
    'products','products_meta','product_variants','orders','orders_product',
    'inventory_reservations','inventory_movements','payment_accounts',
    'payment_sessions','payments','payment_events','payment_refunds',
    'ecommerce_schema_versions'
)
ORDER BY TABLE_NAME;

SELECT TABLE_NAME, COLUMN_TYPE, EXTRA
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id' AND TABLE_NAME IN (
    'products','products_meta','product_variants','orders','orders_product',
    'inventory_reservations','inventory_movements','payment_accounts',
    'payment_sessions','payments','payment_events','payment_refunds',
    'ecommerce_schema_versions'
)
ORDER BY TABLE_NAME;

SELECT 'products' AS table_name, COUNT(*) AS row_count FROM products
UNION ALL SELECT 'products_meta', COUNT(*) FROM products_meta
UNION ALL SELECT 'product_variants', COUNT(*) FROM product_variants
UNION ALL SELECT 'orders', COUNT(*) FROM orders
UNION ALL SELECT 'orders_product', COUNT(*) FROM orders_product
UNION ALL SELECT 'inventory_reservations', COUNT(*) FROM inventory_reservations
UNION ALL SELECT 'inventory_movements', COUNT(*) FROM inventory_movements
UNION ALL SELECT 'payment_accounts', COUNT(*) FROM payment_accounts
UNION ALL SELECT 'payment_sessions', COUNT(*) FROM payment_sessions
UNION ALL SELECT 'payments', COUNT(*) FROM payments
UNION ALL SELECT 'payment_events', COUNT(*) FROM payment_events
UNION ALL SELECT 'payment_refunds', COUNT(*) FROM payment_refunds;

SELECT TABLE_NAME, CONSTRAINT_TYPE, COUNT(*) AS constraint_count
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (
    'products','products_meta','product_variants','orders','orders_product',
    'inventory_reservations','inventory_movements','payment_accounts',
    'payment_sessions','payments','payment_events','payment_refunds',
    'ecommerce_schema_versions'
)
GROUP BY TABLE_NAME, CONSTRAINT_TYPE
ORDER BY TABLE_NAME, CONSTRAINT_TYPE;

SELECT 'orphan_product_meta' AS check_name, COUNT(*) AS violations
FROM products_meta m LEFT JOIN products p ON p.id = m.product_id WHERE p.id IS NULL
UNION ALL
SELECT 'orphan_order_line', COUNT(*)
FROM orders_product l LEFT JOIN orders o ON o.id = l.order_id WHERE o.id IS NULL
UNION ALL
SELECT 'wrong_line_variant', COUNT(*)
FROM orders_product l LEFT JOIN product_variants v ON v.id = l.variant_id AND v.product_id = l.product_id WHERE v.id IS NULL
UNION ALL
SELECT 'wrong_payment_scope', COUNT(*)
FROM payments p LEFT JOIN payment_sessions s ON s.id = p.payment_session_id
    AND s.payment_account_id = p.payment_account_id AND s.currency = p.currency
WHERE s.id IS NULL;

-- Ejecutar cuando el backend ya mantenga agregados transaccionales.
-- Solo pedidos confirmados o posteriores: los borradores pueden estar incompletos.
SELECT o.id AS inconsistent_order_id
FROM orders o
LEFT JOIN (
    SELECT order_id, SUM(subtotal) AS subtotal, SUM(discount_total) AS discount_total,
           SUM(tax_total) AS line_tax_total
    FROM orders_product GROUP BY order_id
) l ON l.order_id = o.id
WHERE o.order_status IN ('confirmed','processing','completed')
  AND (o.subtotal <> COALESCE(l.subtotal,0)
       OR o.discount_total <> COALESCE(l.discount_total,0)
       OR o.tax_total < COALESCE(l.line_tax_total,0));

SELECT v.id AS inconsistent_inventory_variant
FROM product_variants v
LEFT JOIN (
    SELECT variant_id, SUM(quantity) AS reserved
    FROM inventory_reservations WHERE status = 'active' GROUP BY variant_id
) r ON r.variant_id = v.id
WHERE v.track_inventory = 1 AND v.stock_reserved <> COALESCE(r.reserved,0);
