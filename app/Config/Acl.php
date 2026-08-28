<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Acl extends BaseConfig
{
    //Rutas para todos los usuarios
    public $publicRoutes = [
        //APP
        '',
        '/',
        'm',

        'm/info/inicio',

        'm/accounts/login',
        'm/accounts/logout',
        'm/accounts/validate_login_link',
        'm/accounts/signup',
        'm/accounts/logged',
        'm/accounts/signup',
        'm/accounts/recovery',
        'm/accounts/recover',
        'm/accounts/picture',


        'm/info/',
        'm/info/index',
        'm/info/no_permitido',
        'm/info/sits',
        'm/info/suscriptores',


        //API
        'api/accounts/logout',
        'api/accounts/validate',
        'api/accounts/validate_login',
        'api/accounts/create',
        'api/accounts/get_login_link',
        'api/accounts/test',

        'api/sync/sync_table',
        'api/sync/delete_generated_files',
        'api/sync/db_tables_status'
        
    ];

    //Rutas para usuarios con sesión iniciada
    public $loggedRoutes = [
        'm/accounts/profile',
        'm/accounts/edit',

        'm/chat/conversation',

        'api/accounts/update',
        'api/accounts/set_picture',

        'api/users/delete_picture',

        'api/files/upload',
        'api/files/crop',
        'api/files/delete',
        'api/files/update_position',

        'api/posts/create',
        'api/posts/update',
        'api/posts/save',
        'api/posts/delete',
        'api/posts/images',
        'api/posts/set_main_image',

        'api/sits/set_picture',
        'api/sits/albums',

        'm/sits/mis_sits',
        'm/sits/info',
        'm/sits/albums',
        'm/sits/add',
        'm/sits/edit',
        'm/sits/picture',
        'm/sits/edit_albums',

        'm/albums/edit',
        'm/albums/edit_images',
        'm/albums/images',

        'api/conversations/create',

        'api/ai_generate/get_answer',
        'api/ai_generate/delete_messages',
    ];

    //Rutas y sus roles permitidos
    public $routesRoles = [
        'admin/users/explore' => [2],
        'admin/users/edit' => [2]
    ];
}