<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'group' => 'roles'],
            ['name' => 'Read Roles', 'slug' => 'roles.read', 'group' => 'roles'],
            ['name' => 'Update Roles', 'slug' => 'roles.update', 'group' => 'roles'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'group' => 'roles'],
            ['name' => 'Create Permissions', 'slug' => 'permissions.create', 'group' => 'permissions'],
            ['name' => 'Read Permissions', 'slug' => 'permissions.read', 'group' => 'permissions'],
            ['name' => 'Update Permissions', 'slug' => 'permissions.update', 'group' => 'permissions'],
            ['name' => 'Delete Permissions', 'slug' => 'permissions.delete', 'group' => 'permissions'],
            ['name' => 'Read Users', 'slug' => 'users.read', 'group' => 'users'],
            ['name' => 'Update Users', 'slug' => 'users.update', 'group' => 'users'],
            ['name' => 'Assign User Role', 'slug' => 'users.assign_role', 'group' => 'users'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
