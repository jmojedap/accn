<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;

class ApiCors implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest || strpos(ltrim($request->getPath(), '/'), 'api/') !== 0) {
            return null;
        }

        $origin = $request->getHeaderLine('Origin');
        if ($origin === '') {
            return null;
        }

        // Las vistas propias pueden enviar Origin incluso en peticiones del mismo origen.
        // Se compara con la URL configurada, sin confiar en un Host arbitrario.
        $applicationUri = new URI(config('App')->baseURL);
        $applicationOrigin = $applicationUri->getScheme() . '://' . $applicationUri->getAuthority();

        if ($origin === $applicationOrigin) {
            return null;
        }

        $config = config('ApiCors');
        $response = service('response');
        $vary = array_filter(array_map('trim', explode(',', $response->getHeaderLine('Vary'))));
        $vary[] = 'Origin';
        $response->setHeader('Vary', implode(', ', array_unique($vary)));

        if (! in_array($origin, $config->allowedOrigins, true)) {
            return $response->setStatusCode(403)->setJSON([
                'status' => 0,
                'error' => 'origin_not_allowed',
            ]);
        }

        // Se agregan antes de CSRF y Auth para incluirlos también en sus rechazos.
        $response->setHeader('Access-Control-Allow-Origin', $origin);
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        $response->setHeader('Access-Control-Expose-Headers', config('Security')->headerName);

        if (strtoupper($request->getMethod()) !== 'OPTIONS') {
            return null;
        }

        $method = strtoupper($request->getHeaderLine('Access-Control-Request-Method'));
        $headers = array_filter(array_map('trim', explode(',', strtolower(
            $request->getHeaderLine('Access-Control-Request-Headers')
        ))));

        if (! in_array($method, $config->allowedMethods, true)
            || array_diff($headers, array_map('strtolower', $config->allowedHeaders)) !== []) {
            return $response->setStatusCode(403)->setJSON([
                'status' => 0,
                'error' => 'cors_request_not_allowed',
            ]);
        }

        return $response
            ->setHeader('Access-Control-Allow-Methods', implode(', ', $config->allowedMethods))
            ->setHeader('Access-Control-Allow-Headers', implode(', ', $config->allowedHeaders))
            ->setStatusCode(204)
            ->setBody('');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
