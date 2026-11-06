<?php

/**
 * php tests/manual/ecommerce-schema.php [--test|--apply]
 * Prueba v2 en una copia temporal. --apply migra v1 tras respaldo y pruebas.
 * --test tambien verifica una base v2 existente sin escribir en origen.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
require __DIR__ . '/support/ecommerce-client.php';
(new CodeIgniter\Config\DotEnv(getcwd()))->load();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mode = $argv[1] ?? '--test';
assertSchema(in_array($mode, ['--test', '--apply'], true) && count($argv) <= 2, 'Uso: php tests/manual/ecommerce-schema.php [--test|--apply]');
$settings = [
    'host' => getenv('database.default.hostname') ?: 'localhost',
    'user' => getenv('database.default.username') ?: 'root',
    'password' => getenv('database.default.password') ?: '',
    'database' => getenv('database.default.database') ?: 'multisits',
    'port' => (int) (getenv('database.default.port') ?: 3306),
];
$tables = ['products', 'products_meta', 'product_variants', 'orders', 'orders_product', 'payment_sessions', 'payments', 'payment_events', 'ecommerce_schema_versions'];
$removed = ['inventory_reservations', 'inventory_movements', 'payment_accounts', 'payment_refunds'];
$sqlFile = getcwd() . '/app/Database/SQL/2026-11-06_ecommerce_v2_mvp.sql';
$clientDirectory = dirname(PHP_BINARY, 2) . '/mysql/bin/';
foreach ([$sqlFile, $clientDirectory . 'mysql.exe', $clientDirectory . 'mysqldump.exe'] as $path) {
    assertSchema(is_file($path), 'Archivo requerido no encontrado: ' . $path);
}

function importMvp(string $client, array $settings, string $database, string $file): array
{
    return mysqlProcess(array_merge([$client], clientOptions($settings), ['--batch', '--database=' . $database]), $settings, $file);
}

function backupMvp(string $dump, array $settings, array $tables): string
{
    $directory = getcwd() . '/writable/database/backups';
    if (! is_dir($directory)) {
        assertSchema(mkdir($directory, 0700, true), 'No se pudo crear la carpeta de respaldos.');
    }
    $file = $directory . '/ecommerce_before_v2_' . gmdate('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.sql';
    $options = ['--single-transaction', '--skip-lock-tables', '--skip-add-locks', '--skip-comments', '--hex-blob', '--triggers', '--result-file=' . $file];
    $result = mysqlProcess(array_merge([$dump], clientOptions($settings), $options, [$settings['database']], $tables), $settings);
    assertSchema($result['exit'] === 0 && is_file($file) && filesize($file) > 100, 'Respaldo fallido: ' . $result['error']);
    return $file;
}

function inspectMvp(mysqli $db, array $tables, array $removed): array
{
    $names = "'" . implode("','", $tables) . "'";
    $rows = $db->query("SELECT TABLE_NAME,ENGINE,TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ($names)")->fetch_all(MYSQLI_ASSOC);
    assertSchema(count($rows) === 9, 'El MVP requiere nueve tablas, incluida la version.');
    foreach ($rows as $row) {
        assertSchema($row['ENGINE'] === 'InnoDB' && $row['TABLE_COLLATION'] === 'utf8mb4_unicode_ci', 'Motor/collation incorrecto.');
    }
    $removedNames = "'" . implode("','", $removed) . "'";
    assertSchema($db->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ($removedNames)")->num_rows === 0, 'Persisten tablas retiradas.');
    assertSchema($db->query("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ($names) AND COLUMN_NAME IN ('payment_account_id','stock_on_hand','stock_reserved','track_inventory')")->num_rows === 0, 'Persisten columnas retiradas.');
    $ids = $db->query("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ($names) AND COLUMN_NAME='id' AND EXTRA LIKE '%auto_increment%'")->num_rows;
    assertSchema($ids === 9, 'Faltan IDs autoincrementales.');
    assertSchema($db->query("SELECT version FROM ecommerce_schema_versions WHERE version='2026-11-06-v2-mvp'")->num_rows === 1, 'Falta version MVP.');
    return ['tables' => 9, 'autoincrement_ids' => $ids, 'removed_tables' => count($removed)];
}

function seedV1Mvp(mysqli $db): array
{
    $prefix = 'v2_test_' . bin2hex(random_bytes(4));
    $db->query("INSERT INTO products (idcode,name,slug) VALUES ('$prefix','Compra historica','$prefix')");
    $product = $db->insert_id;
    $db->query("INSERT INTO product_variants (product_id,sku) VALUES ($product,'$prefix')");
    $variant = $db->insert_id;
    $db->query("INSERT INTO orders (order_code,buyer_name,email,subtotal,total_amount,paid_total,payment_status) VALUES ('$prefix','Prueba','schema@example.invalid',100,100,100,'paid')");
    $order = $db->insert_id;
    $db->query("INSERT INTO orders_product (order_id,product_id,variant_id,product_name,sku,quantity,unit_price,subtotal,line_total) VALUES ($order,$product,$variant,'Nombre historico','$prefix',1,100,100,100)");
    $db->query("INSERT INTO payment_accounts (provider,account_code,environment) VALUES ('$prefix','default','sandbox')");
    $account = $db->insert_id;
    $db->query("INSERT INTO payment_sessions (order_id,payment_account_id,merchant_reference,idempotency_key,amount,currency) VALUES ($order,$account,'$prefix','$prefix',100,'COP')");
    $session = $db->insert_id;
    $db->query("INSERT INTO payments (payment_session_id,payment_account_id,provider_payment_id,status,amount,captured_amount,currency) VALUES ($session,$account,'$prefix','approved',100,100,'COP')");
    $payment = $db->insert_id;
    $db->query("UPDATE payments SET updated_at='2026-01-01 00:00:00' WHERE id=$payment");
    $db->query("INSERT INTO payment_events (payment_account_id,payment_id,event_type,deduplication_key,payload) VALUES ($account,$payment,'payment.updated','$prefix','{}')");
    $event = $db->insert_id;
    $db->query("INSERT INTO payment_events (payment_account_id,event_type,deduplication_key,payload) VALUES ($account,'payment.updated','unmatched-$prefix','{}')");
    $unmatched = $db->insert_id;
    return compact('prefix', 'product', 'variant', 'order', 'account', 'session', 'payment', 'event', 'unmatched');
}

function exerciseMvp(mysqli $db): int
{
    $prefix = 'mvp_' . bin2hex(random_bytes(4));
    $before = (int) $db->query('SELECT COUNT(*) AS n FROM orders')->fetch_assoc()['n'];
    $checks = 0;
    $reject = static function (string $sql, int $code) use ($db, &$checks): void {
        rejectSql($db, $sql, $code, 'Integridad MVP');
        $checks++;
    };
    $db->begin_transaction();
    try {
        $db->query("INSERT INTO products (idcode,name,slug) VALUES ('$prefix','Acceso virtual','$prefix')");
        $product = $db->insert_id;
        assertSchema($db->query("SELECT product_kind FROM products WHERE id=$product")->fetch_assoc()['product_kind'] === 'digital', 'El tipo predeterminado no es digital.');
        $checks++;
        $db->query("UPDATE products SET product_kind='access' WHERE id=$product");
        $db->query("INSERT INTO product_variants (product_id,sku) VALUES ($product,'$prefix')");
        $variant = $db->insert_id;
        $reject("INSERT INTO products (idcode,name,slug) VALUES ('$prefix','Duplicado','other-$prefix')", 1062);
        $reject("INSERT INTO products_meta (product_id,meta_key,value_json) VALUES (4294967295,'access','{}')", 1452);
        $reject("INSERT INTO products_meta (product_id,meta_key,value_json) VALUES ($product,'access','invalid-json')", 4025);
        $db->query("INSERT INTO orders (order_code,buyer_name,email,subtotal,total_amount,paid_total,payment_status) VALUES ('$prefix','Prueba','schema@example.invalid',100,100,100,'paid')");
        $order = $db->insert_id;
        $lineSql = "INSERT INTO orders_product (order_id,product_id,variant_id,product_name,sku,quantity,unit_price,subtotal,line_total)";
        $db->query($lineSql . " VALUES ($order,$product,$variant,'Snapshot','$prefix',1,100,100,100)");
        $reject($lineSql . " VALUES ($order,$product,$variant,'Snapshot','$prefix',0,0,0,0)", 4025);
        $reject("UPDATE orders SET total_amount=99 WHERE id=$order", 4025);
        $reject("DELETE FROM orders WHERE id=$order", 1451);
        $sessionSql = "INSERT INTO payment_sessions (order_id,provider,environment,merchant_reference,idempotency_key,amount,currency)";
        $db->query($sessionSql . " VALUES ($order,'wompi','sandbox','$prefix','$prefix',100,'COP')");
        $session = $db->insert_id;
        $reject($sessionSql . " VALUES ($order,'wompi','sandbox','other-$prefix','$prefix',100,'COP')", 1062);
        $reject($sessionSql . " VALUES ($order,'wompi','sandbox','usd-$prefix','usd-$prefix',100,'USD')", 1452);
        $reject($sessionSql . " VALUES ($order,'wompi','invalid','$prefix','$prefix',100,'COP')", 4025);
        $paymentSql = "INSERT INTO payments (payment_session_id,provider,environment,provider_payment_id,idempotency_key,status,amount,captured_amount,currency)";
        $db->query($paymentSql . " VALUES ($session,'wompi','sandbox','no-$prefix',NULL,'declined',100,0,'COP')");
        $db->query($paymentSql . " VALUES ($session,'wompi','sandbox','$prefix','$prefix','approved',100,100,'COP')");
        $payment = $db->insert_id;
        assertSchema((int) $db->query("SELECT COUNT(*) AS n FROM payments WHERE payment_session_id=$session")->fetch_assoc()['n'] === 2, 'No se conservan reintentos.');
        $checks++;
        $reject($paymentSql . " VALUES ($session,'wompi','sandbox','$prefix',NULL,'pending',100,0,'COP')", 1062);
        $reject($paymentSql . " VALUES ($session,'wompi','sandbox','key-$prefix','$prefix','pending',100,0,'COP')", 1062);
        $reject($paymentSql . " VALUES ($session,'payu','sandbox','$prefix',NULL,'pending',100,0,'COP')", 1452);
        $reject($paymentSql . " VALUES ($session,'wompi','production','$prefix',NULL,'pending',100,0,'COP')", 1452);
        foreach ([['wompi', 'production'], ['payu', 'sandbox']] as [$provider, $environment]) {
            $db->query($sessionSql . " VALUES ($order,'$provider','$environment','$prefix','$prefix',100,'COP')");
            $otherSession = $db->insert_id;
            $db->query($paymentSql . " VALUES ($otherSession,'$provider','$environment','$prefix','$prefix','declined',100,0,'COP')");
            $checks++;
        }
        $eventSql = "INSERT INTO payment_events (provider,environment,payment_id,event_type,deduplication_key,payload)";
        $db->query($eventSql . " VALUES ('wompi','sandbox',$payment,'payment.updated','$prefix','{}')");
        $event = $db->insert_id;
        $reject($eventSql . " VALUES ('wompi','sandbox',$payment,'payment.updated','$prefix','{}')", 1062);
        $reject($eventSql . " VALUES ('payu','sandbox',$payment,'payment.updated','$prefix','{}')", 1452);
        $reject($eventSql . " VALUES ('wompi','production',$payment,'payment.updated','$prefix','{}')", 1452);
        $reject("UPDATE payment_events SET processing_status='processed',processed_at=UTC_TIMESTAMP(6) WHERE id=$event", 4025);
        $db->query("UPDATE payment_events SET signature_valid=1,processing_status='processed',processed_at=UTC_TIMESTAMP(6) WHERE id=$event");
        $db->query($eventSql . " VALUES ('wompi','production',NULL,'payment.updated','$prefix','{}')");
        $checks++;
        $reject("UPDATE payments SET refunded_amount=1 WHERE id=$payment", 4025);
        $reject("UPDATE payments SET refunded_amount=101,refund_reference='manual',refunded_at=UTC_TIMESTAMP(6) WHERE id=$payment", 4025);
        $db->query("UPDATE payments SET refunded_amount=10,refund_reference='manual-$prefix',refunded_at=UTC_TIMESTAMP(6),refund_reason='Gestion manual',status='partially_refunded' WHERE id=$payment AND refunded_amount=0");
        assertSchema($db->affected_rows === 1, 'No se pudo registrar devolucion manual.');
        $db->query("UPDATE orders SET refunded_total=10,payment_status='partially_refunded' WHERE id=$order");
        $db->query("UPDATE payments SET refunded_amount=10 WHERE id=$payment AND refunded_amount=0");
        assertSchema($db->affected_rows === 0, 'Se volvio a aplicar la devolucion.');
        $checks += 2;
    } finally {
        $db->rollback();
    }
    assertSchema((int) $db->query('SELECT COUNT(*) AS n FROM orders')->fetch_assoc()['n'] === $before, 'ROLLBACK no conservo el estado inicial.');
    return $checks + 1;
}

$source = connectEcommerce($settings, $settings['database']);
$installed = $source->query("SELECT version FROM ecommerce_schema_versions WHERE version='2026-11-06-v2-mvp'")->num_rows === 1;
assertSchema(! ($installed && $mode === '--apply'), 'El MVP ya esta aplicado; para verificarlo usar --test.');
$sourceTables = $installed ? $tables : array_merge($tables, $removed);
$backup = backupMvp($clientDirectory . 'mysqldump.exe', $settings, $sourceTables);
echo 'Respaldo: ' . $backup . PHP_EOL;
$temporary = 'accn_ecommerce_test_' . bin2hex(random_bytes(6));
assertSchema((bool) preg_match('/^accn_ecommerce_test_[a-f0-9]{12}$/', $temporary) && $temporary !== $settings['database'], 'Base temporal insegura.');
$source->query('CREATE DATABASE `' . $temporary . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
try {
    $restored = importMvp($clientDirectory . 'mysql.exe', $settings, $temporary, $backup);
    assertSchema($restored['exit'] === 0, 'Restauracion fallida: ' . $restored['error']);
    $test = connectEcommerce($settings, $temporary);
    if (! $installed) {
        $fixture = seedV1Mvp($test);
        $guard = static function (string $message) use ($test, $clientDirectory, $settings, $temporary, $sqlFile): void {
            $result = importMvp($clientDirectory . 'mysql.exe', $settings, $temporary, $sqlFile);
            assertSchema($result['exit'] !== 0 && str_contains($result['error'], $message), 'Proteccion fallida: ' . $message . ': ' . $result['error']);
            assertSchema($test->query("SHOW COLUMNS FROM payment_sessions LIKE 'provider'")->num_rows === 0, 'Se alteraron tablas antes de validar precondiciones.');
            $test->query('DROP PROCEDURE IF EXISTS accn_simplify_ecommerce_20261106');
        };
        $test->query("UPDATE product_variants SET stock_on_hand=1 WHERE id={$fixture['variant']}");
        $guard('Existe inventario');
        $test->query("UPDATE product_variants SET stock_on_hand=0 WHERE id={$fixture['variant']}");
        $test->query("INSERT INTO payment_refunds (payment_id,payment_account_id,idempotency_key,amount,currency) VALUES ({$fixture['payment']},{$fixture['account']},'guard-refund',1,'COP')");
        $refund = $test->insert_id;
        $guard('Existen devoluciones');
        $test->query("DELETE FROM payment_refunds WHERE id=$refund");
        $test->query("INSERT INTO payment_accounts (provider,account_code,environment) VALUES ('{$fixture['prefix']}','second','sandbox')");
        $account = $test->insert_id;
        $guard('Hay varias cuentas');
        $test->query("DELETE FROM payment_accounts WHERE id=$account");
        $test->query('CREATE TABLE mvp_dependency (session_id BIGINT UNSIGNED, CONSTRAINT fk_mvp_extra_dependency FOREIGN KEY (session_id) REFERENCES payment_sessions(id)) ENGINE=InnoDB');
        $guard('dependencias adicionales');
        $test->query('DROP TABLE mvp_dependency');
        $preservedCounts = [];
        foreach (array_diff($tables, ['ecommerce_schema_versions']) as $table) {
            $preservedCounts[$table] = $test->query("SELECT COUNT(*) AS n FROM `$table`")->fetch_assoc()['n'];
        }
        $migrated = importMvp($clientDirectory . 'mysql.exe', $settings, $temporary, $sqlFile);
        assertSchema($migrated['exit'] === 0, 'Migracion temporal fallida: ' . $migrated['error']);
        foreach ($preservedCounts as $table => $count) {
            assertSchema($test->query("SELECT COUNT(*) AS n FROM `$table`")->fetch_assoc()['n'] === $count, 'Se altero el conteo de ' . $table);
        }
        $preserved = $test->query("SELECT provider,environment,captured_amount,updated_at FROM payments WHERE id={$fixture['payment']}")->fetch_assoc();
        assertSchema($preserved['provider'] === $fixture['prefix'] && $preserved['environment'] === 'sandbox' && $preserved['captured_amount'] === '100.00', 'No se conservo el pago historico.');
        assertSchema(str_starts_with($preserved['updated_at'], '2026-01-01 00:00:00'), 'Se altero la fecha historica del pago.');
        assertSchema($test->query("SELECT provider FROM payment_events WHERE id={$fixture['unmatched']}")->fetch_assoc()['provider'] === $fixture['prefix'], 'Se perdio la identidad de evento no asociado.');
        echo 'Migracion con datos y cuatro protecciones previas: OK' . PHP_EOL;
    }
    $schema = inspectMvp($test, $tables, $removed);
    $repeat = importMvp($clientDirectory . 'mysql.exe', $settings, $temporary, $sqlFile);
    assertSchema($repeat['exit'] !== 0 && str_contains($repeat['error'], 'ya esta instalado'), 'No se impidio repetir la migracion.');
    $test->query('DROP PROCEDURE IF EXISTS accn_simplify_ecommerce_20261106');
    echo json_encode(['temporary_schema' => $schema, 'behavior_checks' => exerciseMvp($test), 'repeat_guard' => true, 'backup_restore' => true], JSON_PRETTY_PRINT), PHP_EOL;
    $test->close();
} finally {
    $source->query('DROP DATABASE `' . $temporary . '`');
}

if ($mode === '--apply') {
    $backup = backupMvp($clientDirectory . 'mysqldump.exe', $settings, $sourceTables);
    echo 'Respaldo final: ' . $backup . PHP_EOL;
    $result = importMvp($clientDirectory . 'mysql.exe', $settings, $settings['database'], $sqlFile);
    assertSchema($result['exit'] === 0, 'Fallo la migracion. Conservar respaldo y revisar estado parcial: ' . $result['error']);
    echo json_encode(['applied_to' => $settings['database'], 'schema' => inspectMvp($source, $tables, $removed), 'backup' => $backup], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
} else {
    echo 'Pruebas correctas; origen sin cambios.' . PHP_EOL;
}
