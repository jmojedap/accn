<?php

// Utilidades CLI de MySQL compartidas por las pruebas de ecommerce.

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

