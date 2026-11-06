<?php

/**
 * Instalador historico de v1. Para el MVP actual usar ecommerce-schema.php.
 * Uso: php tests/manual/ecommerce-schema-v1.php [--test|--apply]
 * Requiere los clientes mysql y mysqldump de XAMPP y permiso CREATE DATABASE.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
(new CodeIgniter\Config\DotEnv(getcwd()))->load();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mode = $argv[1] ?? '--test';
if (! in_array($mode, ['--test', '--apply'], true) || count($argv) > 2) {
    throw new InvalidArgumentException('Uso: php tests/manual/ecommerce-schema.php [--test|--apply]');
}

$settings = [
    'host' => getenv('database.default.hostname') ?: 'localhost',
    'user' => getenv('database.default.username') ?: 'root',
    'password' => getenv('database.default.password') ?: '',
    'database' => getenv('database.default.database') ?: 'multisits',
    'port' => (int) (getenv('database.default.port') ?: 3306),
];
$sqlFile = getcwd() . '/app/Database/SQL/2026-11-06_ecommerce_v1.sql';
$mysqlDirectory = dirname(PHP_BINARY, 2) . '/mysql/bin';
$mysql = $mysqlDirectory . '/mysql.exe';
$dump = $mysqlDirectory . '/mysqldump.exe';
foreach ([$sqlFile, $mysql, $dump] as $requiredFile) {
    if (! is_file($requiredFile)) {
        throw new RuntimeException('Archivo requerido no encontrado: ' . $requiredFile);
    }
}

function connectEcommerce(array $settings, string $database): mysqli
{
    $db = new mysqli($settings['host'], $settings['user'], $settings['password'], $database, $settings['port']);
    $db->set_charset('utf8mb4');
    $db->query("SET time_zone = '+00:00'");
    $db->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION'");

    return $db;
}

function mysqlProcess(array $command, array $settings, ?string $input = null): array
{
    // La clave viaja en el entorno del proceso, nunca en argumentos ni archivos.
    $environment = getenv();
    $environment['MYSQL_PWD'] = $settings['password'];
    $descriptors = [0 => $input === null ? ['pipe', 'r'] : ['file', $input, 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $process = proc_open($command, $descriptors, $pipes, null, $environment, ['bypass_shell' => true]);
    if (! is_resource($process)) {
        throw new RuntimeException('No se pudo iniciar el cliente MySQL.');
    }
    if ($input === null) {
        fclose($pipes[0]);
    }
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    return ['exit' => proc_close($process), 'output' => $output, 'error' => $error];
}

function clientOptions(array $settings): array
{
    return ['--no-defaults', '--protocol=TCP', '--host=' . $settings['host'], '--port=' . $settings['port'], '--user=' . $settings['user'], '--default-character-set=utf8mb4'];
}

function importEcommerce(string $mysql, array $settings, string $database, string $file): array
{
    return mysqlProcess(array_merge([$mysql], clientOptions($settings), ['--batch', '--database=' . $database]), $settings, $file);
}

function backupEcommerce(string $dump, array $settings): string
{
    $directory = getcwd() . '/writable/database/backups';
    if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
        throw new RuntimeException('No se pudo crear la carpeta de respaldos.');
    }
    $path = $directory . '/ecommerce_before_v1_' . gmdate('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.sql';
    $command = array_merge([$dump], clientOptions($settings), [
        '--hex-blob', '--triggers', '--skip-add-locks', '--skip-comments', '--result-file=' . $path,
        $settings['database'], 'orders', 'orders_product', 'products', 'products_meta',
    ]);
    $result = mysqlProcess($command, $settings);
    if ($result['exit'] !== 0 || ! is_file($path) || filesize($path) < 100) {
        throw new RuntimeException('Respaldo fallido: ' . $result['error']);
    }

    return $path;
}

function assertSchema(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function rejectSql(mysqli $db, string $sql, int $expectedCode, string $label): void
{
    try {
        $db->query($sql);
    } catch (mysqli_sql_exception $exception) {
        assertSchema($exception->getCode() === $expectedCode, $label . ': error inesperado ' . $exception->getMessage());
        return;
    }
    throw new RuntimeException($label . ': la base acepto datos invalidos.');
}

function inspectEcommerce(mysqli $db): array
{
    $names = "'products','products_meta','product_variants','orders','orders_product','inventory_reservations','inventory_movements','payment_accounts','payment_sessions','payments','payment_events','payment_refunds','ecommerce_schema_versions'";
    $tables = $db->query("SELECT TABLE_NAME,ENGINE,TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ($names)")->fetch_all(MYSQLI_ASSOC);
    assertSchema(count($tables) === 13, 'Faltan tablas de ecommerce.');
    foreach ($tables as $table) {
        assertSchema($table['ENGINE'] === 'InnoDB' && $table['TABLE_COLLATION'] === 'utf8mb4_unicode_ci', 'Motor o collation incorrecto en ' . $table['TABLE_NAME']);
    }
    $ids = $db->query("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ($names) AND COLUMN_NAME='id' AND EXTRA LIKE '%auto_increment%'")->num_rows;
    assertSchema($ids === 13, 'Falta AUTO_INCREMENT en alguna tabla.');
    $triggers = (int) $db->query("SELECT COUNT(*) AS n FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND EVENT_OBJECT_TABLE='orders'")->fetch_assoc()['n'];
    assertSchema($triggers === 0, 'Permanece un trigger heredado de orders.');
    assertSchema($db->query("SELECT version FROM ecommerce_schema_versions")->fetch_assoc()['version'] === '2026-11-06-v1', 'Version de esquema incorrecta.');

    return ['tables' => count($tables), 'autoincrement_ids' => $ids, 'legacy_order_triggers' => $triggers];
}

function exerciseEcommerce(mysqli $db): int
{
    $checks = 0;
    $db->begin_transaction();
    try {
        $db->query("INSERT INTO products (idcode,name,slug,base_price,tax_percent) VALUES ('TEST-P1','Producto prueba','test-producto-1',10000,19)");
        $product = $db->insert_id;
        $db->query("INSERT INTO product_variants (product_id,sku,stock_on_hand) VALUES ($product,'TEST-SKU1',10)");
        $variant = $db->insert_id;
        $db->query("INSERT INTO products (idcode,name,slug) VALUES ('TEST-P2','Otro producto','test-producto-2')");
        $otherProduct = $db->insert_id;
        $db->query("INSERT INTO product_variants (product_id,sku) VALUES ($otherProduct,'TEST-SKU2')");
        $otherVariant = $db->insert_id;
        rejectSql($db, "INSERT INTO products (idcode,name,slug) VALUES ('TEST-P1','Duplicado','otro-slug')", 1062, 'Codigo unico');
        rejectSql($db, "INSERT INTO products (idcode,name,slug) VALUES ('TEST-P3','Duplicado','test-producto-1')", 1062, 'Slug unico');
        rejectSql($db, "INSERT INTO products (idcode,name,slug) VALUES ('TEST-P3','Sin slug','')", 4025, 'Slug requerido');
        rejectSql($db, "INSERT INTO products_meta (product_id,meta_key,value_json) VALUES (4294967295,'color','\"azul\"')", 1452, 'Metadato huerfano');
        rejectSql($db, "INSERT INTO products_meta (product_id,meta_key,value_json) VALUES ($product,'color','json-invalido')", 4025, 'JSON valido');
        $db->query("INSERT INTO products_meta (product_id,meta_key,position,value_json) VALUES ($product,'color',0,'\"azul\"'),($product,'color',1,'\"rojo\"')");
        $checks += 6;

        $db->query("INSERT INTO orders (order_code,buyer_name,email,subtotal,tax_total,total_amount) VALUES ('TEST-O1','Prueba','schema@example.invalid',10000,1900,11900)");
        $order = $db->insert_id;
        $lineSql = "INSERT INTO orders_product (order_id,product_id,variant_id,product_name,sku,quantity,unit_price,subtotal,tax_percent,tax_total,line_total)";
        $db->query($lineSql . " VALUES ($order,$product,$variant,'Nombre historico','TEST-SKU1',1,10000,10000,19,1900,11900)");
        $line = $db->insert_id;
        rejectSql($db, $lineSql . " VALUES ($order,$product,$otherVariant,'Producto','TEST-SKU2',1,0,0,0,0,0)", 1452, 'Variante de otro producto');
        rejectSql($db, $lineSql . " VALUES ($order,$product,$variant,'Producto','TEST-SKU1',0,0,0,0,0,0)", 4025, 'Cantidad positiva');
        rejectSql($db, "UPDATE orders SET total_amount=1 WHERE id=$order", 4025, 'Total del pedido');
        rejectSql($db, "DELETE FROM orders WHERE id=$order", 1451, 'Conservacion del pedido');
        $db->query("UPDATE products SET name='Nombre nuevo',deleted_at=UTC_TIMESTAMP(6) WHERE id=$product");
        assertSchema($db->query("SELECT product_name FROM orders_product WHERE id=$line")->fetch_assoc()['product_name'] === 'Nombre historico', 'Se perdio el snapshot de compra.');
        $checks += 5;

        $db->query("INSERT INTO payment_accounts (provider,account_code,environment) VALUES ('wompi','test','sandbox')");
        $account = $db->insert_id;
        $db->query("INSERT INTO payment_accounts (provider,account_code,environment) VALUES ('wompi','test','production')");
        $otherAccount = $db->insert_id;
        $sessionSql = "INSERT INTO payment_sessions (order_id,payment_account_id,merchant_reference,idempotency_key,amount,currency)";
        $db->query($sessionSql . " VALUES ($order,$account,'REF-COMPARTIDA','SESSION-KEY',11900,'COP')");
        $session = $db->insert_id;
        rejectSql($db, $sessionSql . " VALUES ($order,$account,'OTRA-REF','SESSION-KEY',11900,'COP')", 1062, 'Idempotencia de checkout');
        rejectSql($db, $sessionSql . " VALUES ($order,$account,'REF-USD','SESSION-USD',11900,'USD')", 1452, 'Moneda del pedido');
        $paymentSql = "INSERT INTO payments (payment_session_id,payment_account_id,provider_payment_id,status,amount,captured_amount,currency)";
        $db->query($paymentSql . " VALUES ($session,$account,'TX-DECLINADA','declined',11900,0,'COP')");
        $db->query($paymentSql . " VALUES ($session,$account,'TX-APROBADA','approved',11900,11900,'COP')");
        $payment = $db->insert_id;
        assertSchema((int) $db->query("SELECT COUNT(*) AS n FROM payments WHERE payment_session_id=$session")->fetch_assoc()['n'] === 2, 'No permite reintentos bajo una referencia.');
        rejectSql($db, $paymentSql . " VALUES ($session,$account,'TX-APROBADA','pending',11900,0,'COP')", 1062, 'Transaccion externa unica');
        rejectSql($db, $paymentSql . " VALUES ($session,$otherAccount,'TX-OTRA','pending',11900,0,'COP')", 1452, 'Cuenta de la sesion');
        rejectSql($db, "UPDATE payments SET refunded_amount=11901 WHERE id=$payment", 4025, 'Saldo reembolsado');
        $db->query($sessionSql . " VALUES ($order,$otherAccount,'REF-COMPARTIDA','SESSION-KEY',11900,'COP')");
        $otherSession = $db->insert_id;
        $db->query($paymentSql . " VALUES ($otherSession,$otherAccount,'TX-APROBADA','pending',11900,0,'COP')");
        $checks += 7;

        $eventSql = "INSERT INTO payment_events (payment_account_id,payment_id,event_type,deduplication_key,payload)";
        $db->query($eventSql . " VALUES ($account,$payment,'transaction.updated','EVENT-1','{}')");
        $event = $db->insert_id;
        rejectSql($db, $eventSql . " VALUES ($account,$payment,'transaction.updated','EVENT-1','{}')", 1062, 'Webhook duplicado');
        rejectSql($db, $eventSql . " VALUES ($otherAccount,$payment,'transaction.updated','EVENT-2','{}')", 1452, 'Cuenta del webhook');
        rejectSql($db, "UPDATE payment_events SET processing_status='processed',processed_at=UTC_TIMESTAMP(6) WHERE id=$event", 4025, 'Firma de webhook requerida');
        $db->query("UPDATE payment_events SET signature_valid=1,processing_status='processed',processed_at=UTC_TIMESTAMP(6) WHERE id=$event");
        $refundSql = "INSERT INTO payment_refunds (payment_id,payment_account_id,idempotency_key,amount,currency)";
        $db->query($refundSql . " VALUES ($payment,$account,'REFUND-1',1000,'COP')");
        rejectSql($db, $refundSql . " VALUES ($payment,$account,'REFUND-1',1000,'COP')", 1062, 'Idempotencia de devolucion');
        rejectSql($db, $refundSql . " VALUES ($payment,$account,'REFUND-USD',1000,'USD')", 1452, 'Moneda de devolucion');
        rejectSql($db, $refundSql . " VALUES ($payment,$account,'REFUND-NEG',-1,'COP')", 4025, 'Importe de devolucion');
        $checks += 6;

        $db->query("UPDATE product_variants SET stock_reserved=stock_reserved+1 WHERE id=$variant AND stock_on_hand-stock_reserved>=1");
        assertSchema($db->affected_rows === 1, 'No se pudo reservar inventario.');
        $db->query("INSERT INTO inventory_reservations (order_product_id,variant_id,reservation_key,quantity,expires_at) VALUES ($line,$variant,'RES-1',1,UTC_TIMESTAMP(6)+INTERVAL 1 DAY)");
        $reservation = $db->insert_id;
        rejectSql($db, "UPDATE product_variants SET stock_reserved=11 WHERE id=$variant", 4025, 'Inventario insuficiente');
        rejectSql($db, "INSERT INTO inventory_reservations (order_product_id,variant_id,reservation_key,quantity,expires_at) VALUES ($line,$otherVariant,'RES-2',1,UTC_TIMESTAMP(6)+INTERVAL 1 DAY)", 1452, 'Variante de reserva');
        $db->query("INSERT INTO inventory_movements (variant_id,reservation_id,operation_key,movement_type,quantity_delta) VALUES ($variant,$reservation,'SALE-1','sale',-1)");
        rejectSql($db, "INSERT INTO inventory_movements (variant_id,reservation_id,operation_key,movement_type,quantity_delta) VALUES ($variant,$reservation,'SALE-1','sale',-1)", 1062, 'Movimiento duplicado');
        $checks += 4;
    } finally {
        $db->rollback();
    }
    assertSchema((int) $db->query('SELECT COUNT(*) AS n FROM orders')->fetch_assoc()['n'] === 0, 'ROLLBACK no revirtio el pedido.');
    assertSchema((int) $db->query('SELECT COUNT(*) AS n FROM products')->fetch_assoc()['n'] === 0, 'ROLLBACK no revirtio el catalogo.');

    return $checks + 2;
}

$source = connectEcommerce($settings, $settings['database']);
$alreadyInstalled = (int) $source->query("SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='ecommerce_schema_versions'")->fetch_assoc()['n'];
assertSchema($alreadyInstalled === 0, 'El esquema v1 ya existe. No se modifico la base.');
assertSchema((int) $source->query('SELECT COUNT(*) AS n FROM orders')->fetch_assoc()['n'] === 0, 'Existen pedidos: operacion cancelada.');
assertSchema((int) $source->query('SELECT COUNT(*) AS n FROM orders_product')->fetch_assoc()['n'] === 0, 'Existen lineas de pedido: operacion cancelada.');
$unrelatedBefore = $source->query("SELECT TABLE_NAME,TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME NOT IN ('orders','orders_product','products','products_meta') ORDER BY TABLE_NAME")->fetch_all(MYSQLI_ASSOC);
$backup = backupEcommerce($dump, $settings);
echo 'Respaldo: ' . $backup . PHP_EOL;
$testDatabase = 'accn_ecommerce_test_' . bin2hex(random_bytes(6));
assertSchema((bool) preg_match('/^accn_ecommerce_test_[a-f0-9]{12}$/', $testDatabase) && $testDatabase !== $settings['database'], 'Nombre inseguro de base temporal.');
$source->query('CREATE DATABASE `' . $testDatabase . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
try {
    $restored = importEcommerce($mysql, $settings, $testDatabase, $backup);
    assertSchema($restored['exit'] === 0, 'No se pudo restaurar el respaldo de prueba: ' . $restored['error']);
    $test = connectEcommerce($settings, $testDatabase);
    // Crear un pedido heredado valido sin depender de valores implicitos no estrictos.
    $columns = $test->query('SHOW COLUMNS FROM orders')->fetch_all(MYSQLI_ASSOC);
    $fields = [];
    $values = [];
    foreach ($columns as $column) {
        $fields[] = '`' . $column['Field'] . '`';
        $type = strtolower($column['Type']);
        $values[] = str_contains($type, 'date') || str_contains($type, 'timestamp')
            ? "'2026-11-06 00:00:00'"
            : ((str_contains($type, 'int') || str_contains($type, 'decimal')) ? '1' : "'guard-test'");
    }
    $test->query('INSERT INTO orders (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ')');
    $guard = importEcommerce($mysql, $settings, $testDatabase, $sqlFile);
    assertSchema($guard['exit'] !== 0 && str_contains($guard['error'], 'Existen pedidos'), 'La proteccion de pedidos no funciono.');
    assertSchema((int) $test->query('SELECT COUNT(*) AS n FROM orders')->fetch_assoc()['n'] === 1, 'La proteccion altero pedidos.');
    $test->query('TRUNCATE TABLE orders'); // Solo la base temporal; no dispara el trigger roto.
    $test->query('DROP PROCEDURE IF EXISTS accn_install_ecommerce_20261106');
    $installed = importEcommerce($mysql, $settings, $testDatabase, $sqlFile);
    assertSchema($installed['exit'] === 0, 'Fallo SQL en la base temporal: ' . $installed['error']);
    $schema = inspectEcommerce($test);
    $repeated = importEcommerce($mysql, $settings, $testDatabase, $sqlFile);
    assertSchema($repeated['exit'] !== 0 && str_contains($repeated['error'], 'no se permite reinstalar'), 'La proteccion de reinstalacion no funciono.');
    $test->query('DROP PROCEDURE IF EXISTS accn_install_ecommerce_20261106');
    $checks = exerciseEcommerce($test);
    echo json_encode(['temporary_schema' => $schema, 'behavior_checks' => $checks, 'empty_orders_guard' => true, 'reinstall_guard' => true, 'backup_restore' => true], JSON_PRETTY_PRINT), PHP_EOL;
    $test->close();
} finally {
    $source->query('DROP DATABASE `' . $testDatabase . '`');
}

if ($mode === '--apply') {
    // Respaldo actualizado inmediatamente antes de la operacion real.
    $backup = backupEcommerce($dump, $settings);
    echo 'Respaldo final: ' . $backup . PHP_EOL;
    $result = importEcommerce($mysql, $settings, $settings['database'], $sqlFile);
    assertSchema($result['exit'] === 0, 'La instalacion fallo. Conservar respaldo y revisar antes de continuar: ' . $result['error']);
    $schema = inspectEcommerce($source);
    $unrelatedAfter = $source->query("SELECT TABLE_NAME,TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME NOT IN ('orders','orders_product','products','products_meta','product_variants','inventory_reservations','inventory_movements','payment_accounts','payment_sessions','payments','payment_events','payment_refunds','ecommerce_schema_versions') ORDER BY TABLE_NAME")->fetch_all(MYSQLI_ASSOC);
    // TABLE_ROWS de InnoDB es una estimacion: solo comparar el inventario de nombres.
    assertSchema(array_column($unrelatedBefore, 'TABLE_NAME') === array_column($unrelatedAfter, 'TABLE_NAME'), 'Cambio inesperado en tablas ajenas al ecommerce.');
    echo json_encode(['applied_to' => $settings['database'], 'schema' => $schema, 'backup' => $backup, 'unrelated_tables_preserved' => true], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
} else {
    echo 'Pruebas correctas. La base origen no fue modificada.' . PHP_EOL;
}
