<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    public function all(): Collection
    {
        return Permission::all();
    }

    public function grouped(): array
    {
        return Permission::all()->groupBy('group')->toArray();
    }

    public function find(string $id): ?Permission
    {
        return Permission::find($id);
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function update(string $id, array $data): ?Permission
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return null;
        }

        $permission->update($data);

        return $permission->fresh();
    }

    public function delete(string $id): bool
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return false;
        }

        return (bool) $permission->delete();
    }
}
