<?php

/**
 * Prueba ProductModel con SQLite en memoria, sin tocar MySQL.
 * Ejecutar: D:/xampp/php/php.exe -d extension=sqlite3 tests/manual/product-model.php
 */

use App\Models\ProductModel;
use Config\Database;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

chdir(dirname(__DIR__, 2));
require 'system/Test/bootstrap.php';

$db = Database::connect('tests');
if ($db->DBDriver !== 'SQLite3' || $db->database !== ':memory:') {
    throw new RuntimeException('Estas pruebas solo pueden ejecutarse en SQLite en memoria.');
}

$table = $db->prefixTable('products');
$db->query("CREATE TABLE $table (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    idcode TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    status INTEGER NOT NULL DEFAULT 0,
    type_id INTEGER NOT NULL DEFAULT 1,
    product_kind TEXT NOT NULL DEFAULT 'digital',
    description TEXT NULL,
    meta_description TEXT NULL,
    currency TEXT NOT NULL DEFAULT 'COP',
    cost NUMERIC NOT NULL DEFAULT 0,
    base_price NUMERIC NOT NULL DEFAULT 0,
    tax_percent NUMERIC NOT NULL DEFAULT 0,
    weight_grams NUMERIC NOT NULL DEFAULT 0,
    width_cm NUMERIC NOT NULL DEFAULT 0,
    height_cm NUMERIC NOT NULL DEFAULT 0,
    depth_cm NUMERIC NOT NULL DEFAULT 0,
    image_url TEXT NULL,
    creator_id INTEGER NULL,
    updater_id INTEGER NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    deleted_at TEXT NULL
)");

$checks = 0;
function checkProduct(bool $condition, string $message): void
{
    global $checks;

    if (! $condition) {
        throw new RuntimeException($message);
    }

    $checks++;
}

$products = new ProductModel($db);
$input = [
    'idcode' => 'ACCESO-001',
    'name' => 'Acceso al curso',
    'slug' => 'acceso-al-curso',
    'base_price' => '19900.00',
    'deleted_at' => '2000-01-01 00:00:00',
    'created_at' => '2000-01-01 00:00:00',
    'unrecognized_field' => 'ignored',
];
$id = $products->insert($input);
checkProduct(is_numeric($id) && (int) $id > 0, 'Crear producto: ' . json_encode($products->errors()));
$product = $products->find($id);
checkProduct(is_object($product) && $product->name === $input['name'], 'Leer producto como objeto.');
checkProduct($product->product_kind === 'digital' && $product->currency === 'COP', 'Respetar defaults del esquema.');
checkProduct($product->deleted_at === null && $product->created_at !== $input['created_at'], 'Proteger fechas internas del payload.');
checkProduct($products->update($id, ['idcode' => $input['idcode'], 'slug' => $input['slug'], 'name' => 'Acceso actualizado']), 'Actualizar sin falsos duplicados: ' . json_encode($products->errors()));
checkProduct($products->find($id)->name === 'Acceso actualizado', 'Persistir actualizacion parcial.');

$secondId = $products->insert(['idcode' => 'SERVICIO-002', 'name' => 'Asesoria', 'slug' => 'asesoria', 'product_kind' => 'service']);
checkProduct(is_numeric($secondId), 'Crear segundo producto.');
checkProduct($products->update($id, ['id' => $secondId, 'slug' => 'asesoria']) === false && isset($products->errors()['slug']), 'No ignorar duplicados usando un ID ajeno en el payload.');
checkProduct($products->insert(['idcode' => 'OTRO', 'name' => 'Duplicado', 'slug' => 'asesoria']) === false && isset($products->errors()['slug']), 'Validar unicidad al crear.');

foreach ([
    ['name', ''],
    ['base_price', '-0.01'],
    ['cost', '-1'],
    ['tax_percent', '100.01'],
    ['status', 9],
    ['product_kind', 'unknown'],
    ['currency', 'cop'],
] as [$field, $value]) {
    checkProduct($products->update($id, [$field => $value]) === false && isset($products->errors()[$field]), 'Rechazar valor invalido: ' . $field);
}

checkProduct(count($products->findAll()) === 2, 'Listar productos.');
checkProduct($products->delete($id) === true && $products->find($id) === null, 'Borrado logico y exclusion de lecturas.');
$deleted = $products->withDeleted()->find($id);
checkProduct($deleted !== null && $deleted->deleted_at !== null, 'Conservar registro eliminado para historial.');
checkProduct(count($products->findAll()) === 1, 'Excluir eliminados del listado normal.');

$products->update($secondId, ['base_price' => '5000.00', 'currency' => 'USD']);
$accessId = $products->insert([
    'idcode' => 'VIRTUAL-003',
    'name' => 'Acceso inicial',
    'slug' => 'acceso-inicial',
    'status' => 1,
    'type_id' => 2,
    'product_kind' => 'access',
    'base_price' => '10000.00',
    'description' => "Material de O'Brien & acceso 100% virtual",
    'creator_id' => 7,
]);
$courseId = $products->insert([
    'idcode' => 'CURSO-004',
    'name' => 'Curso avanzado',
    'slug' => 'curso-avanzado',
    'base_price' => '20000.00',
    'meta_description' => 'Incluye certificado',
]);
checkProduct(is_numeric($accessId) && is_numeric($courseId), 'Crear datos del buscador.');

$search = $products->search([]);
checkProduct($search['qtyResults'] === 3 && count($search['results']) === 3, 'Conteo y resultados excluyen eliminados.');
checkProduct($search['settings']['perPage'] === 12 && $search['settings']['maxPage'] === 1, 'Mantener configuracion del buscador del proyecto.');
checkProduct(is_object($search['results'][0]) && (int) $search['results'][0]->id === (int) $courseId, 'Objetos y orden descendente predeterminado.');
checkProduct($products->search(['q' => 'Acceso'])['qtyResults'] === 1, 'Buscar por nombre sin devolver el producto eliminado.');
checkProduct($products->search(['q' => 'CURSO-004'])['qtyResults'] === 1, 'Buscar por codigo.');
checkProduct($products->search(['q' => 'certificado'])['qtyResults'] === 1, 'Buscar por metadescripcion.');
checkProduct($products->search(['q' => "acceso O'Brien"])['qtyResults'] === 1, 'Combinar palabras entre campos y escapar apostrofes.');
checkProduct($products->search(['q' => '100%'])['qtyResults'] === 1, 'Escapar comodines de LIKE.');
checkProduct($products->search(['status__eq' => '0'])['qtyResults'] === 2, 'Conservar el filtro de estado cero.');
checkProduct($products->search(['type_id__eq' => '2', 'product_kind__eq' => 'access', 'currency__eq' => 'COP', 'creator_id__eq' => '7'])['qtyResults'] === 1, 'Combinar filtros de productos.');
checkProduct($products->search(['base_price__gte' => '10000', 'base_price__lte' => '10000'])['qtyResults'] === 1, 'Filtrar por rango de precio.');

$page = $products->search(['numPage' => 2, 'perPage' => 2, 'orderField' => 'name', 'orderType' => 'ASC']);
checkProduct($page['qtyResults'] === 3 && $page['settings']['maxPage'] === 2 && $page['settings']['offset'] === 2, 'Paginar sin alterar el total.');
checkProduct(count($page['results']) === 1 && (int) $page['results'][0]->id === (int) $courseId, 'Aplicar orden y desplazamiento.');
$basic = $products->searchResults(['status__eq' => '0'], ['selectFormat' => 'basic', 'perPage' => 1]);
checkProduct(count($basic) === 1 && ! property_exists($basic[0], 'description'), 'Consultar resultados directamente con formato basic.');
checkProduct($products->search(['selectFormat' => 'edition'])['results'][0]->meta_description !== null, 'Formato edition con todos los campos.');

$query = "O'Brien & acceso";
parse_str($products->search(['q' => $query, 'status__eq' => '0'])['getString'], $queryValues);
checkProduct($queryValues['q'] === $query && $queryValues['status__eq'] === '0', 'Codificar parametros GET sin perder caracteres ni ceros.');
$empty = $products->search(['q' => 'inexistente']);
checkProduct($empty['qtyResults'] === 0 && $empty['results'] === [] && $empty['settings']['maxPage'] === 1, 'Respuesta coherente sin coincidencias.');
checkProduct($products->search(['status__eq' => "0' OR 1=1 --"])['qtyResults'] === 0, 'Los filtros no se interpretan como SQL.');
checkProduct($products->search(['q' => "%' OR 1=1 --"])['qtyResults'] === 0, 'La busqueda libre no se interpreta como SQL.');
checkProduct($products->search(['deleted_at__eq' => 'anything'])['qtyResults'] === 3, 'Ignorar filtros no permitidos.');
$invalidSettings = $products->search(['perPage' => -1, 'numPage' => -1, 'orderField' => 'id; DROP TABLE products', 'orderType' => 'anything', 'selectFormat' => 'unknown']);
checkProduct($invalidSettings['settings']['perPage'] === 1 && $invalidSettings['settings']['numPage'] === 1, 'Normalizar paginacion invalida.');
checkProduct($invalidSettings['settings']['orderField'] === 'id' && $invalidSettings['settings']['orderType'] === 'DESC' && $invalidSettings['settings']['selectFormat'] === 'default', 'Restringir orden y formato a valores conocidos.');
checkProduct($products->search(['perPage' => 1000])['settings']['perPage'] === 500, 'Limitar resultados por peticion.');
checkProduct($products->search([])['qtyResults'] === 3, 'No conservar filtros o paginacion de una consulta anterior.');

echo "OK: {$checks} comprobaciones de CRUD, validacion, buscador, paginacion y borrado logico; MySQL sin cambios.\n";
