<?php

/** php tests/manual/admin-menus-head.php: solo render, sin modificar MySQL. */

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

chdir(dirname(__DIR__, 2));
require 'system/Test/bootstrap.php';

Services::injectMock('session', new class {
    private array $values = [];

    public function get(string $key)
    {
        return $this->values[$key] ?? null;
    }

    public function set(string $key, $value): void
    {
        $this->values[$key] = $value;
    }
});

$checks = 0;
foreach ([1, 2, 3, 21, 99] as $role) {
    $_SESSION = ['logged' => true, 'role' => $role, 'user_id' => 77];
    $request = new IncomingRequest(new App(), new URI('http://localhost/accn/admin/ecommerce/products/edit/17'), null, new UserAgent());
    $request->setPath('admin/ecommerce/products/edit/17');
    Services::injectMock('request', $request);
    $head = view('templates/admin_pml_5/main/head', ['headTitle' => 'Prueba de menu']);
    $expected = in_array($role, [1, 2, 3, 21], true) ? $role : 21;

    if (! str_contains($head, 'menus/nav_1_elements_' . $expected . '.js')) {
        throw new RuntimeException('Archivo de menu incorrecto para rol ' . $role);
    }

    preg_match('/var appSection = (.*);/', $head, $match);

    if (json_decode($match[1] ?? '', true) !== 'ecommerce/products/edit/17') {
        throw new RuntimeException('Se perdio la seccion anidada: ' . ($match[1] ?? 'sin appSection'));
    }

    $checks += 2;
}

$_SESSION = ['logged' => false];
$head = view('templates/admin_pml_5/main/head', ['headTitle' => 'Prueba sin sesion']);
if (str_contains($head, 'menus/nav_1_elements_')) {
    throw new RuntimeException('No cargar menus privados sin sesion.');
}
echo 'OK: ' . ($checks + 1) . " comprobaciones de carga por rol y ruta anidada; MySQL sin cambios.\n";
