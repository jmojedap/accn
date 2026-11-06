<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    /**
     * 2025-07-20
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        // getPath() ya es relativo a baseURL, tanto en raiz como en subcarpetas.
        // La ACL identifica las rutas por sus primeros tres segmentos.
        $segments = explode('/', trim($request->getPath(), '/'));
        $currentRoute = implode('/', array_slice($segments, 0, 3));

        //Verificar si el usuario en sesión tiene acceso permitido
        $routeAllowed = $this->routeAllowed($currentRoute);

        //Verificar allow
        if ( $routeAllowed == 0 )
        {
            $viewData['message'] = "Acceso no permitido";
            $viewData['headTitle'] = "Acceso no permitido";
            $viewData['viewA'] = 'm/info/no_permitido';

            // Devolver una respuesta HTTP completa con la vista
            $response = service('response');
            return $response->setStatusCode(403)->setBody(view('templates/easypml/public', $viewData));
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
            //Es desarrollador (1) o administrador (2)
            if ( $session->role == 1 or $session->role == 2 ) return 1;

            //Si está en las rutas para usuarios con sesión
            if ( in_array($currentRoute, $acl->loggedRoutes) ) return 1;

            //Verificar si la función existe en el array de rutas restringidas para ciertos roles
            if ( array_key_exists($currentRoute, $acl->routesRoles) )
            {
                $roles = $acl->routesRoles[$currentRoute];          //Array roles permitidos para la función
                if ( in_array($session->role, $roles) ) return 1;   //El rol está incluido en el array
            }
        }

        return 0;
    }
}
