<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    /**
     * 2023-03-25
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $uri = current_url(true);
        $segments = $uri->getSegments();
        $currentRoute = '';
        //echo count($segments);
        if ( count($segments) == 2 ) $currentRoute =  $uri->getSegment(2, '');
        if ( count($segments) == 3 ) $currentRoute =  $uri->getSegment(2, '') . '/' . $uri->getSegment(3, '');
        if ( count($segments) >= 4 ) $currentRoute =  $uri->getSegment(2, '') . '/' . $uri->getSegment(3, '') . '/' . $uri->getSegment(4, '');

        $routeAllowed = $this->routeAllowed($currentRoute);   //Verificar si el usuario en sesión tiene acceso permitido

        //Verificar allow
        if ( $routeAllowed == 0 )
        {
            echo 'Acceso no permitido: ' . $currentRoute;
            //return redirect()->to(base_url("info/no_permitido/"));
            exit();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }

// Funciones adicionales
//-----------------------------------------------------------------------------

    /**
     * Verificar si el usuario en sesión tiene acceso permitido
     * 2023-03-25
     */
    public function routeAllowed($currentRoute)
    {
        $acl = new \Config\Acl();

        //Verificar en rutas públicas (Sin login)
        if ( in_array($currentRoute, $acl->publicRoutes) ) return 1;

        //Verificar sesión
        $session = \Config\Services::session();
        
        //Si inició sesión
        if ( $session->logged )
        {
            //Es administrador
            if ( $session->role == 1 or $session->role == 2 ) return 1;

            //Si está en las rutas para usuarios con sesión
            if ( in_array($currentRoute, $acl->loggedRoutes) ) return 1;

            //Verificar si la función existe en el array de rutas restringidas para ciertos roles
            if ( array_key_exists($currentRoute, $acl->routesRoles) )
            {
                $roles = $acl->routesRoles[$currentRoute];                //Array roles permitidos para la función
                if ( in_array($session->role, $roles) ) return 1;   //El rol está incluido en el array
            }
        }

        return 0;
    }
}