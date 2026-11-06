-- Solo lectura. Seleccionar la base MVP antes de ejecutar.
SELECT DATABASE() AS database_name, VERSION() AS server_version;
SELECT version, installed_at FROM ecommerce_schema_versions ORDER BY id;

SELECT TABLE_NAME, ENGINE, TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (
    'products','products_meta','product_variants','orders','orders_product',
    'payment_sessions','payments','payment_events','ecommerce_schema_versions'
) ORDER BY TABLE_NAME;

SELECT TABLE_NAME, COLUMN_TYPE, EXTRA
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id' AND TABLE_NAME IN (
    'products','products_meta','product_variants','orders','orders_product',
    'payment_sessions','payments','payment_events','ecommerce_schema_versions'
) ORDER BY TABLE_NAME;

-- Ambas consultas deben devolver cero filas.
SELECT TABLE_NAME AS removed_table_still_present FROM information_schema.TABLES
WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN (
    'inventory_reservations','inventory_movements','payment_accounts','payment_refunds'
);
SELECT TABLE_NAME, COLUMN_NAME AS removed_column_still_present
FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()
  AND TABLE_NAME IN ('product_variants','payment_sessions','payments','payment_events')
  AND COLUMN_NAME IN ('track_inventory','stock_on_hand','stock_reserved','payment_account_id');

SELECT 'products' AS table_name, COUNT(*) AS row_count FROM products
UNION ALL SELECT 'products_meta', COUNT(*) FROM products_meta
UNION ALL SELECT 'product_variants', COUNT(*) FROM product_variants
UNION ALL SELECT 'orders', COUNT(*) FROM orders
UNION ALL SELECT 'orders_product', COUNT(*) FROM orders_product
UNION ALL SELECT 'payment_sessions', COUNT(*) FROM payment_sessions
UNION ALL SELECT 'payments', COUNT(*) FROM payments
UNION ALL SELECT 'payment_events', COUNT(*) FROM payment_events;

SELECT TABLE_NAME, CONSTRAINT_TYPE, COUNT(*) AS constraint_count
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN (
    'products','products_meta','product_variants','orders','orders_product',
    'payment_sessions','payments','payment_events','ecommerce_schema_versions'
)
GROUP BY TABLE_NAME, CONSTRAINT_TYPE ORDER BY TABLE_NAME, CONSTRAINT_TYPE;

SELECT 'orphan_product_meta' AS check_name, COUNT(*) AS violations
FROM products_meta m LEFT JOIN products p ON p.id=m.product_id WHERE p.id IS NULL
UNION ALL
SELECT 'orphan_order_line', COUNT(*)
FROM orders_product l LEFT JOIN orders o ON o.id=l.order_id WHERE o.id IS NULL
UNION ALL
SELECT 'wrong_line_variant', COUNT(*)
FROM orders_product l LEFT JOIN product_variants v ON v.id=l.variant_id AND v.product_id=l.product_id WHERE v.id IS NULL
UNION ALL
SELECT 'wrong_session_currency', COUNT(*)
FROM payment_sessions s LEFT JOIN orders o ON o.id=s.order_id AND o.currency=s.currency WHERE o.id IS NULL
UNION ALL
SELECT 'wrong_payment_scope', COUNT(*)
FROM payments p LEFT JOIN payment_sessions s ON s.id=p.payment_session_id
    AND s.provider=p.provider AND s.environment=p.environment AND s.currency=p.currency
WHERE s.id IS NULL
UNION ALL
SELECT 'wrong_event_scope', COUNT(*)
FROM payment_events e LEFT JOIN payments p ON p.id=e.payment_id
    AND p.provider=e.provider AND p.environment=e.environment
WHERE e.payment_id IS NOT NULL AND p.id IS NULL;

-- Comprobar agregados cuando el backend ya los mantenga transaccionalmente.
SELECT o.id AS inconsistent_order_id
FROM orders o LEFT JOIN (
    SELECT order_id, SUM(subtotal) AS subtotal, SUM(discount_total) AS discount_total,
           SUM(tax_total) AS line_tax_total FROM orders_product GROUP BY order_id
) l ON l.order_id=o.id
WHERE o.order_status IN ('confirmed','processing','completed')
  AND (o.subtotal<>COALESCE(l.subtotal,0)
       OR o.discount_total<>COALESCE(l.discount_total,0)
       OR o.tax_total<COALESCE(l.line_tax_total,0));

SELECT o.id AS inconsistent_payment_totals
FROM orders o LEFT JOIN (
    SELECT s.order_id,SUM(p.captured_amount) AS paid,SUM(p.refunded_amount) AS refunded
    FROM payments p INNER JOIN payment_sessions s ON s.id=p.payment_session_id
    GROUP BY s.order_id
) a ON a.order_id=o.id
WHERE o.paid_total<>COALESCE(a.paid,0) OR o.refunded_total<>COALESCE(a.refunded,0);
