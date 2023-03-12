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

$routes->get('/admin/test/test', "Testing::pmlAdmin");

// ACOUNTS
//-----------------------------------------------------------------------------
$routes->get('/', 'Accounts::login');


$routes->group('accounts', static function ($routes) {
    $routes->get('login', 'Accounts::login');
    $routes->get('logged', 'Accounts::logged');
    $routes->get('logout', 'Accounts::logout');
    $routes->get('profile', 'Accounts::profile');
});

$routes->post('/api/accounts/validate_login', 'Api\Accounts::validateLogin');

// USERS
//-----------------------------------------------------------------------------

    // USERS ADMIN
    //-----------------------------------------------------------------------------
    $routes->group('admin/users', static function ($routes) {
        $routes->get('explore', 'Admin\Users::explore');
        $routes->get('add', 'Admin\Users::add');
        $routes->get('profile/(:num)', 'Admin\Users::profile/$1');
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
