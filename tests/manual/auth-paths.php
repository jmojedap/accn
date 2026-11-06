<?php

/** php tests/manual/auth-paths.php: filtros y ACL, sin base de datos ni peticiones HTTP. */

use App\Filters\Auth;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use Config\Services;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

chdir(dirname(__DIR__, 2));
require 'system/Test/bootstrap.php';

// Captura la decision real de ACL sin renderizar las paginas de rechazo.
final class AuthPathProbe extends Auth
{
    public string $route = '';
    public bool $allowed = false;

    public function routeAllowed($currentRoute)
    {
        $this->route = $currentRoute;
        $this->allowed = (bool) parent::routeAllowed($currentRoute);

        return 1;
    }
}

$session = new class {
    public bool $logged = false;
    public int $role = 0;
};
Services::injectMock('session', $session);

$cases = [
    ['', '', 0, true],
    ['m/accounts/login', 'm/accounts/login', 0, true],
    ['api/accounts/validate_login', 'api/accounts/validate_login', 0, true],
    ['api/accounts/csrf', 'api/accounts/csrf', 0, true],
    ['api/accounts/update', 'api/accounts/update', 0, false],
    ['api/accounts/update', 'api/accounts/update', 21, true],
    ['admin/users/edit/17', 'admin/users/edit', 0, false],
    ['admin/users/edit/17', 'admin/users/edit', 3, false],
    ['admin/users/edit/17', 'admin/users/edit', 2, true],
    ['admin/private/reports/17', 'admin/private/reports', 21, false],
];

$checks = 0;
foreach (['https://www.multisits.com/', 'http://localhost/accn/', 'https://example.test/apps/accn/'] as $baseURL) {
    foreach ($cases as [$path, $expectedRoute, $role, $allowed]) {
        $config = new App();
        $config->baseURL = $baseURL;
        $request = new IncomingRequest($config, new URI($baseURL . $path), null, new UserAgent());
        $request->setPath($path, $config);
        Services::injectMock('request', $request);
        $session->logged = $role !== 0;
        $session->role = $role;

        $filter = new AuthPathProbe();
        $filter->before($request);

        if ($filter->route !== $expectedRoute || $filter->allowed !== $allowed) {
            throw new RuntimeException(sprintf(
                'Base %s, path %s, role %d: route=%s, allowed=%s; expected route=%s, allowed=%s',
                $baseURL,
                $path,
                $role,
                $filter->route,
                json_encode($filter->allowed),
                $expectedRoute,
                json_encode($allowed)
            ));
        }

        $checks++;
    }
}

echo "Auth: {$checks} casos correctos para instalaciones en raiz y subcarpetas.\n";
