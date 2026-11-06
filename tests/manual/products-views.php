<?php

/**
 * php -d extension=sqlite3 tests/manual/products-views.php
 * Prueba vistas/controlador con SQLite y genera paginas sinteticas para QA visual.
 */

use App\Controllers\Admin\Products as AdminProducts;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;

require __DIR__ . '/product-model.php';

final class ProductViewsTestController extends AdminProducts
{
    public array $page = [];

    public function prepare(array $query = []): void
    {
        $this->request = new IncomingRequest(new App(), new URI('http://localhost/accn/admin/ecommerce/products/explore'), null, new UserAgent());
        $this->request->setGlobal('get', $query);
    }

    protected function renderPage(array $data): mixed
    {
        $this->page = $data;

        return view($data['viewA'], $data);
    }
}

$viewChecks = 0;
function checkProductView(bool $condition, string $message): void
{
    global $viewChecks;

    if (! $condition) {
        throw new RuntimeException($message);
    }

    $viewChecks++;
}

function previewProductView(string $name, string $html, array $data): void
{
    $directory = WRITEPATH . 'cache/products-ui';
    if (! is_dir($directory)) {
        mkdir($directory, 0700, true);
    }

    $html = view($data['nav2'], $data) . $html;
    // Estas paginas usan exclusivamente datos de prueba y una API .test interceptada.
    $html = str_replace(json_encode(URL_API . 'products/'), json_encode('https://products.test/api/products/'), $html);
    $html = str_replace(URL_API . 'products/', 'https://products.test/api/products/', $html);
    $html = str_replace(json_encode(URL_ADMIN . 'ecommerce/products/'), json_encode('https://products.test/admin/ecommerce/products/'), $html);
    $html = str_replace(URL_ADMIN . 'ecommerce/products/', 'https://products.test/admin/ecommerce/products/', $html);
    $html = str_replace(esc(URL_ADMIN . 'ecommerce/products/', 'attr'), esc('https://products.test/admin/ecommerce/products/', 'attr'), $html);
    checkProductView(! str_contains($html, json_encode(URL_API . 'products/')), 'Las previews no deben llamar a la API local real.');
    $document = '<!doctype html><html lang="es"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>Productos - QA</title>'
        . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">'
        . '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>'
        . '<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>'
        . '<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.0.0-alpha.1/axios.min.js"></script>'
        . '<script>const {createApp}=Vue;</script>'
        . '<style>[v-cloak]{display:none}</style></head><body class="bg-light">'
        . '<main class="container-fluid p-3 p-md-4" style="max-width:1500px">' . $html
        . '</main></body></html>';
    file_put_contents($directory . '/' . $name . '.html', $document);
}

$controller = new ProductViewsTestController($products);
$controller->prepare();
$explore = $controller->explore();
checkProductView(str_contains($explore, 'id="productsExplore"') && str_contains($explore, 'delete_selected'), 'Renderizar listado y eliminacion multiple.');
checkProductView($controller->page['search']['qtyResults'] === 3, 'El controlador debe usar ProductModel y excluir eliminados.');
checkProductView(! str_contains($explore, 'posts/'), 'No deben quedar enlaces a posts.');
previewProductView('explore', $explore, $controller->page);
file_put_contents(WRITEPATH . 'cache/products-ui/search.json', json_encode($controller->page['search'], JSON_THROW_ON_ERROR));

$controller->prepare(['q' => 'Acceso', 'numPage' => '999']);
$controller->explore();
checkProductView($controller->page['search']['settings']['numPage'] === 1 && $controller->page['search']['qtyResults'] === 1, 'Conservar filtros y corregir pagina fuera de rango.');
$controller->prepare();
$add = $controller->add();
checkProductView(str_contains($add, 'name="base_price"') && str_contains($add, 'productCreatedModal'), 'Renderizar formulario de creacion y confirmacion.');
checkProductView(! str_contains($add, 'stock_') && ! str_contains($add, 'name="title"'), 'El formulario debe corresponder al catalogo virtual.');
previewProductView('add', $add, $controller->page);

$info = $controller->info((int) $accessId);
checkProductView($controller->page['row']->idcode === 'VIRTUAL-003' && $controller->page['productSection'] === 'info', 'Cargar informacion por ID interno.');
checkProductView($controller->page['nav2'] === 'admin/ecommerce/products/menus/menu', 'El detalle debe usar el menu del producto particular.');
$infoMenu = html_entity_decode(view($controller->page['nav2'], $controller->page), ENT_QUOTES | ENT_HTML5, 'UTF-8');
checkProductView(str_contains($infoMenu, 'info/' . $accessId) && str_contains($infoMenu, 'edit/' . $accessId) && str_contains($infoMenu, 'aria-current="page">Información'), 'Menu del detalle con enlaces al mismo producto y pestana activa.');
checkProductView(str_contains($info, 'productInfoApp') && ! str_contains($info, 'v-html'), 'Renderizar detalle con descripcion segura.');
previewProductView('info', $info, $controller->page);

$edit = $controller->edit((int) $accessId);
checkProductView($controller->page['row']->idcode === 'VIRTUAL-003' && $controller->page['formMode'] === 'edit', 'Cargar el producto solicitado para editar.');
checkProductView(str_contains($edit, 'update/') && ! str_contains($edit, 'posts/'), 'Edicion conectada a Products API.');
checkProductView($controller->page['nav2'] === 'admin/ecommerce/products/menus/menu' && str_contains(view($controller->page['nav2'], $controller->page), 'aria-current="page">Editar'), 'Compartir menu particular y activar Editar.');
previewProductView('edit', $edit, $controller->page);

$products->update($accessId, ['description' => '</script><img src=x onerror="window.productXss=true">']);
$escaped = $controller->edit((int) $accessId);
checkProductView(! str_contains($escaped, '</script><img src=x') && str_contains($escaped, '\u003C'), 'Escapar datos embebidos en scripts.');
$escapedInfo = $controller->info((int) $accessId);
checkProductView(! str_contains($escapedInfo, '</script><img src=x') && str_contains($escapedInfo, '\u003C'), 'Escapar contenido del detalle antes de insertarlo en JavaScript.');
$notFound = false;
try {
    $controller->edit((int) $id); // Producto borrado logicamente por el fixture.
} catch (PageNotFoundException $exception) {
    $notFound = true;
}
checkProductView($notFound, 'Los productos eliminados deben responder 404 en edicion.');
foreach ([(int) $id, 999999] as $missingId) {
    $notFound = false;
    try {
        $controller->info($missingId);
    } catch (PageNotFoundException $exception) {
        $notFound = true;
    }
    checkProductView($notFound, 'El detalle debe responder 404 si el producto fue eliminado o no existe.');
}

$routes = Services::routes();
foreach (['admin/ecommerce/products', 'admin/ecommerce/products/explore', 'admin/ecommerce/products/add', 'admin/ecommerce/products/info/([0-9]+)', 'admin/ecommerce/products/edit/([0-9]+)'] as $route) {
    checkProductView(isset($routes->getRoutes('get')[$route]), 'Falta ruta administrativa: ' . $route);
    checkProductView(in_array('auth', (array) ($routes->getRoutesOptions($route, 'get')['filter'] ?? []), true), 'Falta filtro auth en ' . $route);
}
checkProductView((new Config\Acl())->routesRoles['admin/ecommerce/products'] === [1, 2, 3], 'Falta permiso centralizado del modulo anidado.');
echo "OK: {$viewChecks} comprobaciones de vistas/controlador/rutas; previews en writable/cache/products-ui; MySQL sin cambios.\n";
