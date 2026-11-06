<?php

/**
 * php -d extension=sqlite3 tests/manual/products-api.php
 * Reutiliza las pruebas/fixture del modelo; nunca escribe en MySQL.
 */

use App\Controllers\Api\Products;
use App\Filters\Auth;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;

require __DIR__ . '/product-model.php';

final class ProductsApiTestController extends Products
{
    public function prepare(array $data = [], bool $json = false, string $method = 'post', ?string $raw = null): void
    {
        $body = $raw ?? ($json ? json_encode($data, JSON_THROW_ON_ERROR) : null);
        $this->request = new IncomingRequest(new App(), new URI('http://localhost/accn/api/products'), $body, new UserAgent());
        $this->request->setMethod($method);
        $this->request->setGlobal('post', $json || $method === 'get' ? [] : $data);
        $this->request->setGlobal('get', $method === 'get' ? $data : []);

        if ($json) {
            $this->request->setHeader('Content-Type', 'application/json');
        }

        $this->response = new Response(new App());
    }
}

$session = new class {
    public bool $logged = true;
    public int $role = 3;
    public int $user_id = 77;

    public function get(string $key)
    {
        return $this->{$key} ?? null;
    }
};
Services::injectMock('session', $session);
$controller = new ProductsApiTestController($products);
$apiChecks = 0;
function checkProductsApi(bool $condition, string $message): void
{
    global $apiChecks;

    if (! $condition) {
        throw new RuntimeException($message);
    }

    $apiChecks++;
}

function productsBody($response): array
{
    return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
}

$controller->prepare(['name' => 'Servicio API', 'base_price' => '25000.00', 'product_kind' => 'service', 'id' => 999, 'creator_id' => 999, 'updater_id' => 999]);
$response = $controller->create();
$created = productsBody($response);
checkProductsApi($response->getStatusCode() === 201 && $created['savedId'] > 0 && $created['errors'] === [], 'Crear desde formulario: ' . $response->getBody());
$product = $products->find($created['savedId']);
checkProductsApi($product->slug === 'servicio-api' && $product->idcode === $created['idcode'], 'Generar codigo y slug antes de insertar.');
checkProductsApi((int) $product->creator_id === 77 && (int) $product->updater_id === 77 && (int) $product->id !== 999, 'La identidad y auditoria no deben provenir del payload.');

$controller->prepare(['idcode' => 'API-JSON', 'name' => 'Servicio API']);
$second = productsBody($controller->save('0'));
checkProductsApi($second['savedId'] > 0 && $second['idcode'] === 'API-JSON', 'Guardar como creacion y conservar codigo explicito.');
checkProductsApi(str_starts_with($products->find($second['savedId'])->slug, 'servicio-api-'), 'Evitar colision al generar slug.');

$controller->prepare(['name' => 'Acceso JSON', 'product_kind' => 'access'], true);
$jsonCreated = productsBody($controller->create());
checkProductsApi($jsonCreated['savedId'] > 0, 'Admitir creacion JSON desde Axios.');
$countBefore = $db->table('products')->countAllResults();
$controller->prepare([]);
$invalid = productsBody($controller->validateForm());
checkProductsApi($invalid['status'] === 0 && isset($invalid['errors']['name']), 'Validar campos obligatorios sin guardar.');
$controller->prepare(['id' => $created['savedId'], 'idcode' => $created['idcode'], 'slug' => $product->slug]);
checkProductsApi(productsBody($controller->validateForm())['status'] === 1, 'Validar edicion sin falsos duplicados.');
checkProductsApi($db->table('products')->countAllResults() === $countBefore, 'La validacion no debe insertar.');
$controller->prepare(['id' => ['invalid']]);
checkProductsApi($controller->validateForm()->getStatusCode() === 400, 'Rechazar ID de validacion invalido.');
$controller->prepare(['id' => 999999]);
checkProductsApi($controller->validateForm()->getStatusCode() === 404, 'No validar edicion de un producto inexistente.');

$controller->prepare(['id' => $second['savedId'], 'name' => 'Editado por API', 'creator_id' => 999], true);
$updated = productsBody($controller->update($created['idcode']));
checkProductsApi($updated['saved'] && $updated['savedId'] === $created['savedId'], 'Actualizar el producto identificado por la URL.');
checkProductsApi($products->find($created['savedId'])->name === 'Editado por API' && $products->find($second['savedId'])->name === 'Servicio API', 'Ignorar el ID de otro producto en el payload.');
checkProductsApi((int) $products->find($created['savedId'])->creator_id === 77, 'Conservar creador al actualizar.');
$controller->prepare(['slug' => $products->find($second['savedId'])->slug]);
$response = $controller->save($created['idcode']);
checkProductsApi($response->getStatusCode() === 422 && isset(productsBody($response)['errors']['slug']), 'Devolver errores de unicidad.');
$controller->prepare(['name' => ['invalid']], true);
$response = $controller->create();
checkProductsApi($response->getStatusCode() === 422 && isset(productsBody($response)['errors']['name']), 'Rechazar campos estructurados antes de la validacion del framework.');
$controller->prepare(['base_price' => '-1']);
checkProductsApi($controller->update($created['idcode'])->getStatusCode() === 422, 'Validar precios en actualizaciones.');
$controller->prepare([]);
checkProductsApi($controller->update('NO-EXISTE')->getStatusCode() === 404, 'No actualizar filas inexistentes.');
checkProductsApi(productsBody($controller->info($created['idcode']))['row']['name'] === 'Editado por API', 'Consultar por codigo alfanumerico.');
checkProductsApi($controller->info('NO-EXISTE')->getStatusCode() === 404, 'Consulta inexistente devuelve 404.');

$controller->prepare(['q' => 'Editado', 'perPage' => 1], false, 'get');
$search = productsBody($controller->search());
checkProductsApi($search['qtyResults'] === 1 && $search['results'][0]['idcode'] === $created['idcode'], 'Buscar mediante GET con paginacion.');
$controller->prepare(['q' => 'Acceso JSON'], true);
checkProductsApi(productsBody($controller->search())['qtyResults'] === 1, 'Buscar mediante POST JSON.');
$controller->prepare(['q' => 'Acceso JSON'], true, 'get', '');
checkProductsApi(productsBody($controller->search())['qtyResults'] === 1, 'Buscar por GET con cabecera JSON y cuerpo vacio.');

foreach (['{invalid', '[]', 'null'] as $raw) {
    $controller->prepare([], true, 'post', $raw);
    checkProductsApi($controller->create()->getStatusCode() === 400, 'Rechazar JSON invalido o que no sea un objeto.');
}
foreach ([[], ['ids' => 'API-JSON'], ['ids' => [$second['idcode'], '']], ['ids' => [['nested']]], ['ids' => array_fill(0, 501, 'API-JSON')]] as $payload) {
    $controller->prepare($payload, true, 'delete');
    checkProductsApi($controller->deleteSelected()->getStatusCode() === 400, 'Rechazar seleccion invalida antes de eliminar.');
}
checkProductsApi($products->find($second['savedId']) !== null, 'Una seleccion invalida no debe eliminar parcialmente.');
$controller->prepare(['ids' => [$second['idcode'], $second['idcode'], 'NO-EXISTE']], true, 'delete');
$bulk = productsBody($controller->deleteSelected());
checkProductsApi($bulk['summary'] === ['total' => 2, 'deleted' => 1, 'not_deleted' => 1], 'Resumen de eliminacion multiple y deduplicacion.');
checkProductsApi($products->find($second['savedId']) === null && $products->withDeleted()->find($second['savedId']) !== null, 'Eliminar logicamente los productos seleccionados.');
$controller->prepare([], false, 'delete');
checkProductsApi(productsBody($controller->delete($created['idcode']))['code'] === 'DELETED', 'Eliminar un producto individual.');
checkProductsApi($controller->delete($created['idcode'])->getStatusCode() === 404, 'No volver a eliminar un producto oculto.');
$controller->prepare(['q' => 'Editado']);
checkProductsApi(productsBody($controller->search())['qtyResults'] === 0, 'Excluir eliminados de la API de busqueda.');

$routes = Services::routes();
$apiRoutes = [
    ['get', 'api/products'],
    ['get', 'api/products/search'],
    ['post', 'api/products/search'],
    ['get', 'api/products/info/([^/]+)'],
    ['post', 'api/products/validate'],
    ['post', 'api/products/create'],
    ['post', 'api/products/update/([^/]+)'],
    ['post', 'api/products/save'],
    ['post', 'api/products/save/([^/]+)'],
    ['delete', 'api/products/delete_selected'],
    ['delete', 'api/products/delete/([^/]+)'],
];
foreach ($apiRoutes as [$verb, $route]) {
    checkProductsApi(isset($routes->getRoutes($verb)[$route]), 'Ruta ausente: ' . $verb . ' ' . $route);
    $filters = (array) ($routes->getRoutesOptions($route, $verb)['filter'] ?? []);
    checkProductsApi(in_array('auth', $filters, true), 'Ruta sin filtro auth: ' . $route);
}
$auth = new Auth();
$acl = new Config\Acl();
foreach ($acl->routesRoles as $route => $roles) {
    if (! str_starts_with($route, 'api/products')) {
        continue;
    }

    foreach ([1, 2, 3, 4] as $role) {
        $session->role = $role;
        checkProductsApi((bool) $auth->routeAllowed($route) === in_array($role, [1, 2, 3], true), 'Permisos incorrectos: ' . $route);
    }

    $session->logged = false;
    checkProductsApi($auth->routeAllowed($route) === 0, 'Una ruta de catalogo no debe admitir anonimos.');
    $session->logged = true;
}

echo "OK: {$apiChecks} comprobaciones de API, formularios/JSON, auditoria, rutas y ACL; MySQL sin cambios.\n";
