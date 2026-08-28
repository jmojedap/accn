<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Limita solicitudes por dirección IP y ruta mediante el throttler nativo.
 */
class RateLimit implements FilterInterface
{
    /**
     * Consume un token del límite configurado como "throttle:capacidad,segundos".
     *
     * @param list<string>|null $arguments Capacidad y ventana de reposición.
     *
     * @return ResponseInterface|null
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $capacity = max(1, (int) ($arguments[0] ?? 10));
        $seconds = max(1, (int) ($arguments[1] ?? 60));
        $bucket = hash('sha256', $request->getIPAddress() . '|' . uri_string());
        $throttler = Services::throttler();

        if ($throttler->check($bucket, $capacity, $seconds)) {
            return null;
        }

        $retryAfter = $throttler->getTokenTime();

        return service('response')
            ->setStatusCode(429)
            ->setHeader('Retry-After', (string) $retryAfter)
            ->setJSON([
                'status' => 0,
                'error' => 'rate_limit_exceeded',
                'message' => 'Demasiadas solicitudes. Intente nuevamente más tarde.',
                'retry_after' => $retryAfter,
            ]);
    }

    /**
     * No modifica la respuesta; se implementa por contrato de FilterInterface.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
