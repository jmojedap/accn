<?php

namespace Config;

/**
 * Define todas las rutas HTTP y declara su política de autorización.
 *
 * Las rutas sin opción filter son públicas. Toda ruta protegida usa una de las
 * políticas centralizadas en Acl para evitar reconstruir o comparar URLs.
 */

// Obtiene la colección de rutas administrada por CodeIgniter.
$routes = Services::routes();

// Impide que métodos de controlador no declarados sean accesibles por URL.
$routes->setAutoRoute(false);

// Configura el namespace y los valores de resolución predeterminados.
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('M\Accounts');
$routes->setDefaultMethod('login');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// Construye una sola vez las opciones de filtro usadas por los grupos protegidos.
$acl = new Acl();
$developerAccess = ['filter' => $acl->filterFor(Acl::POLICY_DEVELOPER)];
$managementAccess = ['filter' => $acl->filterFor(Acl::POLICY_MANAGEMENT)];
$contentAccess = ['filter' => $acl->filterFor(Acl::POLICY_CONTENT)];
$authenticatedAccess = ['filter' => $acl->filterFor(Acl::POLICY_AUTHENTICATED)];

/*
 * Rutas públicas generales.
 *
 * Inicio, información pública y página informativa de acceso denegado.
 */
$routes->get('', 'M\Info::inicio');
$routes->get('/', 'M\Info::inicio');
$routes->get('m', 'M\Info::inicio');
$routes->get('m/info/no_permitido', 'M\Info::noPermitido');

$routes->group('m/info', static function ($routes): void {
    // Contenido público principal de la aplicación.
    $routes->get('inicio', 'M\Info::inicio');
    $routes->get('sits', 'M\Info::exploreSits');
    $routes->get('suscriptores', 'M\Info::suscriptores');
});

/*
 * Herramientas transversales protegidas.
 */
$routes->post('tools/get_unique_slug', 'Api\Tools::getUniqueSlug', $contentAccess);
$routes->get(
    'admin/tools/preview_emails/(:any)/(:num)',
    'Admin\Tools::previewEmails/$1/$2',
    $managementAccess
);
$routes->post(
    'admin/tools/master_login/(:num)',
    'Admin\Tools::masterLogin/$1',
    $developerAccess
);

/*
 * Cuentas públicas.
 *
 * Estas páginas y endpoints son necesarios antes de que exista una sesión.
 */
$routes->group('m/accounts', static function ($routes): void {
    $routes->get('login', 'M\Accounts::login');
    $routes->get('login/(:any)', 'M\Accounts::login/$1');
    $routes->get('validate_login_link/(:any)', 'M\Accounts::validateLoginLink/$1');
    $routes->get('signup', 'M\Accounts::signup');
    $routes->get('picture', 'M\Accounts::picture');
    $routes->get('picture/(:any)', 'M\Accounts::picture/$1');
});

$routes->group('api/accounts', static function ($routes): void {
    // Validación, login, magic link y registro requieren acceso anónimo.
    $routes->post('validate', 'Api\Accounts::validateForm', ['filter' => 'throttle:60,60']);
    $routes->post('validate_login', 'Api\Accounts::validateLogin', ['filter' => 'throttle:10,60']);
    $routes->post('get_login_link', 'Api\Accounts::getLoginLink', ['filter' => 'throttle:5,300']);
    $routes->post('create', 'Api\Accounts::create', ['filter' => 'throttle:5,3600']);
});

/*
 * Cuenta del usuario autenticado.
 */
$routes->group('m/accounts', $authenticatedAccess, static function ($routes): void {
    // Logout usa POST porque destruye la sesión actual.
    $routes->post('logout', 'M\Accounts::logout');
    $routes->post('logout/ajax', 'M\Accounts::logout/ajax');
    $routes->get('logged', 'M\Accounts::logged');
    $routes->get('profile', 'M\Accounts::profile');
});

$routes->group('api/accounts', $authenticatedAccess, static function ($routes): void {
    // Operaciones que modifican los datos de la cuenta activa.
    $routes->post('update', 'Api\Accounts::update');
    $routes->post('set_picture', 'Api\Accounts::setPicture');
});

/*
 * Sincronización y estado de base de datos.
 *
 * Solo developer y admin pueden consultar o ejecutar estas operaciones.
 */
$routes->group('admin/sync', $managementAccess, static function ($routes): void {
    $routes->get('panel', 'Admin\Sync::panel');
});

$routes->group('api/sync', $managementAccess, static function ($routes): void {
    // Las operaciones que escriben archivos o datos nunca se exponen con GET.
    $routes->post('generate_files/(:any)', 'Api\Sync::generateFiles/$1');
    $routes->post('sync_table/(:any)', 'Api\Sync::syncTable/$1');
    $routes->delete('delete_generated_files/(:any)', 'Api\Sync::deleteGeneratedFiles/$1');
    $routes->get('db_tables_status', 'Api\Sync::dbTablesStatus');
    $routes->post('update_rows_count', 'Api\Sync::updateRowsCount');
});

/*
 * Administración de usuarios.
 *
 * El editor no administra cuentas ni roles.
 */
$routes->group('admin/users', $managementAccess, static function ($routes): void {
    $routes->get('explore', 'Admin\Users::explore');
    $routes->get('add', 'Admin\Users::add');
    $routes->get('profile/(:num)', 'Admin\Users::profile/$1');
    $routes->get('details/(:num)', 'Admin\Users::details/$1');
    $routes->get('edit/(:num)', 'Admin\Users::edit/$1');
});

$routes->group('api/users', $managementAccess, static function ($routes): void {
    $routes->post('search', 'Api\Users::search');
    $routes->post('validate', 'Api\Users::validateForm');
    $routes->post('create', 'Api\Users::create');
    $routes->post('update/(:num)', 'Api\Users::update/$1');
    $routes->post('delete_selected', 'Api\Users::deleteSelected');
    $routes->delete('delete_picture', 'Api\Users::deletePicture');
});

/*
 * Administración de posts.
 *
 * Developer, admin y editor pueden gestionar contenido editorial.
 */
$routes->group('admin/posts', $contentAccess, static function ($routes): void {
    $routes->get('explore', 'Admin\Posts::explore');
    $routes->get('add', 'Admin\Posts::add');
    $routes->get('info/(:num)', 'Admin\Posts::info/$1');
    $routes->get('details/(:num)', 'Admin\Posts::details/$1');
    $routes->get('edit/(:num)', 'Admin\Posts::edit/$1');
    $routes->get('images/(:num)', 'Admin\Posts::images/$1');
});

$routes->group('api/posts', $contentAccess, static function ($routes): void {
    $routes->post('search', 'Api\Posts::search');
    $routes->post('validate', 'Api\Posts::validateForm');
    $routes->post('create', 'Api\Posts::create');
    $routes->post('update/(:num)', 'Api\Posts::update/$1');
    $routes->post('save/(:num)', 'Api\Posts::save/$1');
    $routes->delete('delete_selected', 'Api\Posts::deleteSelected');
    $routes->delete('delete/(:num)', 'Api\Posts::delete/$1');
    $routes->get('images/(:num)', 'Api\Posts::images/$1');
    $routes->put('set_main_image/(:num)/(:num)', 'Api\Posts::setMainImage/$1/$2');
});

/*
 * Administración de archivos.
 */
$routes->group('admin/files', $contentAccess, static function ($routes): void {
    $routes->get('explore', 'Admin\Files::explore');
    $routes->get('add', 'Admin\Files::add');
    $routes->get('index/(:num)', 'Admin\Files::index/$1');
    $routes->get('info/(:num)', 'Admin\Files::info/$1');
    $routes->get('details/(:num)', 'Admin\Files::details/$1');
    $routes->get('edit/(:num)', 'Admin\Files::edit/$1');
    $routes->get('cropping/(:num)', 'Admin\Files::cropping/$1');
});

$routes->group('api/files', $contentAccess, static function ($routes): void {
    $routes->post('search', 'Api\Files::search');
    $routes->post('validate', 'Api\Files::validateForm');
    $routes->post('upload', 'Api\Files::upload');
    $routes->post('update/(:num)', 'Api\Files::update/$1');
    $routes->post('delete_selected', 'Api\Files::deleteSelected');
    $routes->delete('delete/(:num)', 'Api\Files::delete/$1');
    $routes->put('update_position/(:num)/(:num)', 'Api\Files::updatePosition/$1/$2');
    $routes->post('crop/(:num)', 'Api\Files::crop/$1');
});

/*
 * Catálogos de ítems usados en formularios y contenido.
 */
$routes->group('admin/items', $contentAccess, static function ($routes): void {
    $routes->get('values/(:num)', 'Admin\Items::values/$1');
    $routes->get('values/(:num)/(:any?)', 'Admin\Items::values/$1/$2');
    $routes->get('add', 'Admin\Items::add');
});

$routes->group('api/items', $contentAccess, static function ($routes): void {
    $routes->post('search', 'Api\Items::search');
    $routes->get('search', 'Api\Items::search');
    $routes->post('get_list', 'Api\Items::getList');
    $routes->post('save/(:num)', 'Api\Items::save/$1');
    $routes->delete('delete_row/(:num)/(:num)', 'Api\Items::deleteRow/$1/$2');
    $routes->post('update_items_list_file', 'Api\Items::updateItemsListFile');
});

/*
 * Conversaciones y generación asistida por IA.
 */
$routes->group('api/conversations', $contentAccess, static function ($routes): void {
    $routes->post('create', 'Api\AiConversations::create');
});

$routes->group('m/chat', $contentAccess, static function ($routes): void {
    $routes->get('conversation/(:num)', 'M\Chat::conversation/$1');
});

$routes->group('api/ai_generate', $contentAccess, static function ($routes): void {
    $routes->post('get_answer', 'Api\AiGenerate::getAnswer');
    $routes->delete('delete_messages', 'Api\AiGenerate::deleteMessages');
});

/*
 * Gestión de álbumes del usuario autenticado.
 */
$routes->group('m/albums', $contentAccess, static function ($routes): void {
    $routes->get('mis_albums', 'M\Albums::myAlbums');
    $routes->get('edit/(:num)', 'M\Albums::edit/$1');
    $routes->get('edit_images/(:num)', 'M\Albums::editImages/$1');
    $routes->get('images/(:num)', 'M\Albums::images/$1');
});

/*
 * Gestión de sits y sus recursos asociados.
 */
$routes->group('m/sits', $contentAccess, static function ($routes): void {
    $routes->get('mis_sits', 'M\Sits::mySits');
    $routes->get('info/(:any)', 'M\Sits::info/$1');
    $routes->get('albums/(:any)', 'M\Sits::albums/$1');
    $routes->get('add', 'M\Sits::add');
    $routes->get('edit/(:num)', 'M\Sits::edit/$1');
    $routes->get('picture/(:num)/(:any)', 'M\Sits::picture/$1/$2');
    $routes->get('edit_albums/(:num)', 'M\Sits::editAlbums/$1');
});

$routes->group('api/sits', $contentAccess, static function ($routes): void {
    $routes->post('set_picture', 'Api\Sits::setPicture');
    $routes->get('albums/(:num)', 'Api\Sits::Albums/$1');
    $routes->delete('delete/(:num)', 'Api\Sits::delete/$1');
});

/*
 * Permite sobreescrituras específicas por entorno después de las rutas base.
 */
$environmentRoutes = APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
if (is_file($environmentRoutes)) {
    require $environmentRoutes;
}
