<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Rbac::permissionNames() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (Rbac::roles() as $name => $permissions) {
            $role = Role::findOrCreate($name, 'web');
            // Only ever *add* defaults, so permissions an admin granted in the UI are never revoked by re-seeding.
            $role->givePermissionTo($permissions);
        }

        // Backfill: accounts created before RBAC carry their role only in users.role.
        User::whereIn('role', Rbac::builtInRoles())->each(function (User $user) {
            if (! $user->roles()->exists()) {
                $user->assignRole($user->role);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
