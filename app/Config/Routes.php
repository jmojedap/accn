<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Accounts');
$routes->setDefaultMethod('login');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

// ----------------------------- GENERAL ------------------------------------------------
//---------------------------------------------------------------------------------------
$routes->get('', 'M\Info::inicio');
$routes->get('/', 'M\Info::inicio');
$routes->get('/m', 'M\Info::inicio');
$routes->get('m/info/no_permitido', 'M\Info::noPermitido');
$routes->post('tools/get_unique_slug', 'Api\Tools::getUniqueSlug');

$routes->get('admin/tools/preview_emails/(:any)/(:num)', 'Admin\Tools::previewEmails/$1/$2');
$routes->get('admin/tools/master_login/(:num)', 'Admin\Tools::masterLogin/$1');

// ----------------------------- INFO -----------------------------------------------
//-----------------------------------------------------------------------------------

$routes->group('m/info', static function ($routes) {
    $routes->get('inicio', 'M\Info::inicio');
    $routes->get('sits', 'M\Info::exploreSits');
    $routes->get('suscriptores', 'M\Info::suscriptores');
});

// ----------------------------- ACCOUNTS -----------------------------------------------
//---------------------------------------------------------------------------------------

// ACOUNTS ADMIN
//-----------------------------------------------------------------------------

    $routes->group('m/accounts', static function ($routes) {
        $routes->get('login', 'M\Accounts::login');
        $routes->get('login/(:any)', 'M\Accounts::login/$1');
        $routes->get('validate_login_link/(:any)', 'M\Accounts::validateLoginLink/$1');
        $routes->get('signup', 'M\Accounts::signup');
        $routes->get('logged', 'M\Accounts::logged');
        $routes->get('master_login/(:num)', 'M\Accounts::masterLogin/$1');
        $routes->get('logout', 'M\Accounts::logout');
        $routes->get('profile', 'M\Accounts::profile');
        $routes->get('picture', 'M\Accounts::picture');
        $routes->get('picture/(:any)', 'M\Accounts::picture/$1');
    });

// ACCOUNTS API
//-----------------------------------------------------------------------------
    $routes->group('api/accounts', static function ($routes) {
        $routes->post('validate', 'Api\Accounts::validateForm');
        $routes->post('validate_login', 'Api\Accounts::validateLogin');
        $routes->post('get_login_link', 'Api\Accounts::getLoginLink');
        $routes->post('create', 'Api\Accounts::create');
        $routes->post('update', 'Api\Accounts::update');
        $routes->post('set_picture', 'Api\Accounts::setPicture');
        $routes->get('test', 'Api\Accounts::test');
    });

// -------------------------- SYNC -------------------------------------------------
//-----------------------------------------------------------------------------------

// SYNC ADMIN
//-------------------------------------------------------------------------
    $routes->group('admin/sync', static function ($routes) {
        $routes->get('panel', 'Admin\Sync::panel');
    });

// SYNC API
//-----------------------------------------------------------------------------
    $routes->group('api/sync', static function ($routes) {
        $routes->get('generate_files/(:any)', 'Api\Sync::generateFiles/$1');
        $routes->get('sync_table/(:any)', 'Api\Sync::syncTable/$1');
        $routes->get('delete_generated_files/(:any)', 'Api\Sync::deleteGeneratedFiles/$1');
    });

// -------------------------- USERS -------------------------------------------------
//-----------------------------------------------------------------------------------

// USERS ADMIN
//-------------------------------------------------------------------------
    $routes->group('admin/users', static function ($routes) {
        $routes->get('explore', 'Admin\Users::explore');
        $routes->get('add', 'Admin\Users::add');
        $routes->get('profile/(:num)', 'Admin\Users::profile/$1');
        $routes->get('details/(:num)', 'Admin\Users::details/$1');
        $routes->get('edit/(:num)', 'Admin\Users::edit/$1');
    });

// USERS API
//-----------------------------------------------------------------------------
    $routes->group('api/users', static function ($routes) {
        $routes->post('search', 'Api\Users::search');
        $routes->post('validate', 'Api\Users::validateForm');
        $routes->post('create', 'Api\Users::create');
        $routes->post('update/(:num)', 'Api\Users::update/$1');
        $routes->post('delete_selected', 'Api\Users::deleteSelected');
        $routes->delete('delete_picture', 'Api\Users::deletePicture');
    });
    
// -------------------------- POSTS -------------------------------------------------
//-----------------------------------------------------------------------------------

// POSTS ADMIN
//-------------------------------------------------------------------------
    $routes->group('admin/posts', static function ($routes) {
        $routes->get('explore', 'Admin\Posts::explore');
        $routes->get('add', 'Admin\Posts::add');
        $routes->get('info/(:num)', 'Admin\Posts::info/$1');
        $routes->get('details/(:num)', 'Admin\Posts::details/$1');
        $routes->get('edit/(:num)', 'Admin\Posts::edit/$1');
        $routes->get('images/(:num)', 'Admin\Posts::images/$1');
    });

// POSTS API
//-----------------------------------------------------------------------------
    $routes->group('api/posts', static function ($routes) {
        $routes->post('search', 'Api\Posts::search');
        $routes->post('validate', 'Api\Posts::validateForm');
        $routes->post('create', 'Api\Posts::create');
        $routes->post('update/(:num)', 'Api\Posts::update/$1');
        $routes->post('save/(:num)', 'Api\Posts::save/$1');
        $routes->post('delete_selected', 'Api\Posts::deleteSelected');
        $routes->delete('delete/(:num)', 'Api\Posts::delete/$1');
        $routes->get('images/(:num)', 'Api\Posts::images/$1');
        $routes->get('set_main_image/(:num)/(:num)', 'Api\Posts::setMainImage/$1/$2');
    });

// ---------------------------- FILES  --------------------------------------------------
//---------------------------------------------------------------------------------------

// FILES ADMIN
//-------------------------------------------------------------------------
    $routes->group('admin/files', static function ($routes) {
        $routes->get('explore', 'Admin\Files::explore');
        $routes->get('add', 'Admin\Files::add');
        $routes->get('index/(:num)', 'Admin\Files::index/$1');
        $routes->get('info/(:num)', 'Admin\Files::info/$1');
        $routes->get('details/(:num)', 'Admin\Files::details/$1');
        $routes->get('edit/(:num)', 'Admin\Files::edit/$1');
        $routes->get('cropping/(:num)', 'Admin\Files::cropping/$1');
    });

// FILES API
//-----------------------------------------------------------------------------
    $routes->group('api/files', static function ($routes) {
        $routes->post('search', 'Api\Files::search');
        $routes->post('validate', 'Api\Files::validateForm');
        $routes->post('upload', 'Api\Files::upload');
        $routes->post('update/(:num)', 'Api\Files::update/$1');
        $routes->post('delete_selected', 'Api\Files::deleteSelected');
        $routes->delete('delete/(:num)', 'Api\Files::delete/$1');
        $routes->put('update_position/(:num)/(:num)', 'Api\Files::updatePosition/$1/$2');
        $routes->post('crop/(:num)', 'Api\Files::crop/$1');
    });

// ---------------------------- ITEMS  --------------------------------------------------
//---------------------------------------------------------------------------------------

// ITEMS ADMIN
//-----------------------------------------------------------------------------
    $routes->group('admin/items', static function ($routes) {
        $routes->get('values/(:num)', 'Admin\Items::values/$1');
        $routes->get('values/(:num)/(:any?)', 'Admin\Items::values/$1/$2');
        $routes->get('add', 'Admin\Items::add');
    });

// ITEMS API
//-----------------------------------------------------------------------------
    $routes->group('api/items', static function ($routes) {
        $routes->post('search', 'Api\Items::search');
        $routes->get('search', 'Api\Items::search');
        $routes->post('get_list', 'Api\Items::getList');
        $routes->post('save/(:num)', 'Api\Items::save/$1');
        $routes->get('delete_row/(:num)/(:num)', 'Api\Items::deleteRow/$1/$2');
        $routes->get('update_items_list_file', 'Api\Items::updateItemsListFile');
    });

// ---------------------------- AI GENERATE ---------------------------------------------
//---------------------------------------------------------------------------------------

    $routes->group('m/chat', static function ($routes) {
        $routes->get('conversation', 'M\Chat::conversation');
    });

// AI GENERATE API
//-----------------------------------------------------------------------------
    $routes->group('api/ai_generate', static function ($routes) {
        $routes->post('get_answer', 'Api\AiGenerate::getAnswer');
        $routes->delete('delete_messages', 'Api\AiGenerate::deleteMessages');
    });

// ---------------------------- SITS ----------------------------------------------------
//---------------------------------------------------------------------------------------
    $routes->group('m/sits', static function ($routes) {
        $routes->get('mis_sits', 'M\Sits::mySits');
        $routes->get('info/(:any)', 'M\Sits::info/$1');
        $routes->get('photo_albums/(:any)', 'M\Sits::photoAlbums/$1');
        $routes->get('add', 'M\Sits::add');
        $routes->get('edit/(:num)', 'M\Sits::edit/$1');
        $routes->get('picture/(:num)', 'M\Sits::picture/$1');
        $routes->get('edit_photo_albums/(:num)', 'M\Sits::editPhotoAlbums/$1');
    });

// SITS API
//-----------------------------------------------------------------------------
    $routes->group('api/sits', static function ($routes) {
        $routes->post('set_picture', 'Api\Sits::setPicture');
    });

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
