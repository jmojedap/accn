<?php

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Acl;
use Config\Services;

/**
 * Verifica que las políticas de rol y las rutas sensibles mantengan su contrato.
 *
 * @internal
 */
final class AuthorizationConfigTest extends CIUnitTestCase
{
    /** Colección aislada de rutas cargada para cada prueba. */
    private RouteCollection $routes;

    /**
     * Carga Routes.php sin reutilizar el servicio compartido de otra prueba.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Services::resetSingle('routes');
        $this->routes = Services::routes();
        $this->routes->loadRoutes();
    }

    /**
     * Confirma la jerarquía acordada: developer, admin y editor.
     */
    public function testRolePoliciesUseExpectedHierarchy(): void
    {
        $acl = new Acl();

        $this->assertSame([1], $acl->rolesFor(Acl::POLICY_DEVELOPER));
        $this->assertSame([1, 2], $acl->rolesFor(Acl::POLICY_MANAGEMENT));
        $this->assertSame([1, 2, 3], $acl->rolesFor(Acl::POLICY_CONTENT));
        $this->assertSame('auth:1,2,3', $acl->filterFor(Acl::POLICY_CONTENT));
    }

    /**
     * Evita que una actualización reactive el acceso automático a controladores.
     */
    public function testAutoRoutingRemainsDisabled(): void
    {
        $this->assertFalse($this->routes->shouldAutoRoute());
    }

    /**
     * Comprueba que todas las rutas administrativas declaren el filtro auth.
     */
    public function testEveryAdminRouteRequiresAuthorization(): void
    {
        foreach (['get', 'post', 'put', 'delete', 'patch'] as $verb) {
            foreach ($this->routes->getRoutes($verb) as $route => $handler) {
                if (strpos($route, 'admin/') !== 0) {
                    continue;
                }

                $filter = $this->routes->getRoutesOptions($route, $verb)['filter'] ?? '';

                $this->assertStringStartsWith(
                    'auth:',
                    $filter,
                    "La ruta " . strtoupper($verb) . " {$route} debe declarar una política auth."
                );
            }
        }
    }

    /**
     * Permite solo los cuatro endpoints anónimos de cuentas sin filtro auth.
     */
    public function testEveryPrivateApiRouteRequiresAuthorization(): void
    {
        $publicApiRoutes = [
            'api/accounts/validate',
            'api/accounts/validate_login',
            'api/accounts/get_login_link',
            'api/accounts/create',
        ];

        foreach (['get', 'post', 'put', 'delete', 'patch'] as $verb) {
            foreach ($this->routes->getRoutes($verb) as $route => $handler) {
                if (strpos($route, 'api/') !== 0 || in_array($route, $publicApiRoutes, true)) {
                    continue;
                }

                $filter = $this->routes->getRoutesOptions($route, $verb)['filter'] ?? '';

                $this->assertStringStartsWith(
                    'auth:',
                    $filter,
                    "La API privada " . strtoupper($verb) . " {$route} debe usar auth."
                );
            }
        }
    }

    /**
     * Comprueba que login, magic link, validación y registro tengan rate limit.
     */
    public function testPublicAccountApisAreRateLimited(): void
    {
        foreach ([
            'api/accounts/validate',
            'api/accounts/validate_login',
            'api/accounts/get_login_link',
            'api/accounts/create',
        ] as $route) {
            $filter = $this->routes->getRoutesOptions($route, 'post')['filter'] ?? '';

            $this->assertStringStartsWith(
                'throttle:',
                $filter,
                "La API pública {$route} debe limitar solicitudes."
            );
        }
    }

    /**
     * Confirma que las mutaciones críticas no vuelvan a exponerse mediante GET.
     */
    public function testCriticalMutationsAreNotGetRoutes(): void
    {
        $getRoutes = array_keys($this->routes->getRoutes('get'));
        $forbiddenGetFragments = [
            'master_login',
            'sync_table',
            'delete_generated_files',
            'generate_files',
            'update_rows_count',
            'set_main_image',
            'delete_row',
            'update_items_list_file',
            'accounts/logout',
        ];

        foreach ($forbiddenGetFragments as $fragment) {
            $matches = array_filter(
                $getRoutes,
                static fn (string $route): bool => strpos($route, $fragment) !== false
            );

            $this->assertSame([], array_values($matches), "{$fragment} no debe aceptar GET.");
        }
    }
}
