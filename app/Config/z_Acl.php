<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use InvalidArgumentException;

/**
 * Centraliza los roles y las políticas reutilizables de autorización.
 *
 * Las rutas públicas no usan filtro. Las rutas protegidas solicitan una de
 * estas políticas desde Routes.php y el filtro Auth valida la sesión y el rol.
 */
class Acl extends BaseConfig
{
    /** Rol técnico con acceso a todas las áreas protegidas. */
    public const ROLE_DEVELOPER = 1;

    /** Rol responsable de la administración funcional de la aplicación. */
    public const ROLE_ADMIN = 2;

    /** Rol de edición de contenido; se deja preparado aunque aún no exista. */
    public const ROLE_EDITOR = 3;

    /** Política exclusiva para tareas técnicas de alto privilegio. */
    public const POLICY_DEVELOPER = 'developer';

    /** Política para administración, usuarios, configuración y sincronización. */
    public const POLICY_MANAGEMENT = 'management';

    /** Política para las áreas donde también podrá trabajar el futuro editor. */
    public const POLICY_CONTENT = 'content';

    /** Política para cualquier usuario con un rol reconocido por la aplicación. */
    public const POLICY_AUTHENTICATED = 'authenticated';

    /**
     * Relaciona cada política con los roles autorizados.
     *
     * @var array<string, list<int>>
     */
    public array $rolePolicies = [
        self::POLICY_DEVELOPER => [
            self::ROLE_DEVELOPER,
        ],
        self::POLICY_MANAGEMENT => [
            self::ROLE_DEVELOPER,
            self::ROLE_ADMIN,
        ],
        self::POLICY_CONTENT => [
            self::ROLE_DEVELOPER,
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
        ],
        self::POLICY_AUTHENTICATED => [
            self::ROLE_DEVELOPER,
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
        ],
    ];

    /**
     * Devuelve los roles asociados a una política conocida.
     *
     * @return list<int>
     */
    public function rolesFor(string $policy): array
    {
        if (! array_key_exists($policy, $this->rolePolicies)) {
            throw new InvalidArgumentException("La política ACL '{$policy}' no está definida.");
        }

        return $this->rolePolicies[$policy];
    }

    /**
     * Construye la expresión de filtro que entiende CodeIgniter.
     *
     * Ejemplo: la política content produce "auth:1,2,3".
     */
    public function filterFor(string $policy): string
    {
        return 'auth:' . implode(',', $this->rolesFor($policy));
    }

    /**
     * Informa si un número corresponde a un rol reconocido por el sistema.
     */
    public function isKnownRole(int $role): bool
    {
        return in_array($role, $this->rolePolicies[self::POLICY_AUTHENTICATED], true);
    }
}
