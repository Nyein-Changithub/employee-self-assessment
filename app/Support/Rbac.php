<?php

namespace App\Support;

/**
 * Single source of truth for the built-in roles and permissions.
 * Routes and sidebar reference these names, so they are protected from rename/delete in the UI.
 */
final class Rbac
{
    public const SUPER_ROLE = 'admin';

    /** Permission => [group label, description]. */
    public const PERMISSIONS = [
        'dashboard.view' => 'Dashboard',
        'periods.manage' => 'Assessment Periods',
        'questions.manage' => 'Questions',
        'submissions.view' => 'Submissions',
        'submissions.export' => 'Submissions',
        'departments.manage' => 'Master Data',
        'positions.manage' => 'Master Data',
        'users.manage' => 'Access Control',
        'roles.manage' => 'Access Control',
        'permissions.manage' => 'Access Control',
    ];

    /** Permissions that only the admin role holds by default. */
    private const ADMIN_ONLY = ['users.manage', 'roles.manage', 'permissions.manage'];

    public static function permissionNames(): array
    {
        return array_keys(self::PERMISSIONS);
    }

    /** Default role => permissions. `admin` additionally bypasses every check via Gate::before. */
    public static function roles(): array
    {
        $oversight = array_values(array_diff(self::permissionNames(), self::ADMIN_ONLY));

        return [
            'admin' => self::permissionNames(),
            'ceo' => $oversight,
            'gm' => $oversight,
        ];
    }

    public static function builtInRoles(): array
    {
        return array_keys(self::roles());
    }

    public static function isBuiltInPermission(string $name): bool
    {
        return isset(self::PERMISSIONS[$name]);
    }

    public static function isBuiltInRole(string $name): bool
    {
        return in_array($name, self::builtInRoles(), true);
    }
}
