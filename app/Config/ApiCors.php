<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ApiCors extends BaseConfig
{
    /** Orígenes autorizados, sin ruta ni barra final. */
    public array $allowedOrigins = [
        'http://localhost:5173',
    ];

    public array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    public array $allowedHeaders = ['Content-Type', 'X-CSRF-TOKEN'];
}
