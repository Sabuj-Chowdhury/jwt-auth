<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function all(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function find(string $id): ?Role
    {
        return Role::with('permissions')->find($id);
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(string $id, array $data): ?Role
    {
        $role = Role::find($id);

        if (!$role) {
            return null;
        }

        $role->update($data);

        return $role->fresh('permissions');
    }

    public function delete(string $id): bool
    {
        $role = Role::find($id);

        if (!$role || $role->is_system) {
            return false;
        }

        return (bool) $role->delete();
    }

    public function syncPermissions(string $id, array $permissionIds): ?Role
    {
        $role = Role::find($id);

        if (!$role) {
            return null;
        }

        $role->permissions()->sync($permissionIds);

        return $role->fresh('permissions');
    }
}
