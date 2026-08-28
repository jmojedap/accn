<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Publica el token CSRF vigente para que los clientes AJAX puedan reutilizarlo.
 */
class CsrfToken implements FilterInterface
{
    /**
     * No realiza trabajo antes de la solicitud; la validación pertenece al filtro CSRF nativo.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    /**
     * Adjunta el token vigente a todas las respuestas de la aplicación.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $headerName = config('Security')->headerName;

        return $response->setHeader($headerName, csrf_hash());
    }
}
