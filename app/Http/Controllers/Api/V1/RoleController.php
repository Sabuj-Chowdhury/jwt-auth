<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\SyncPermissionsRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Responses\ApiResponse;
use App\Services\RoleService;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index()
    {
        return $this->success('Success', $this->roleService->all());
    }

    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->create($request->validated());

        return $this->success('Role created successfully.', $role, 201);
    }

    public function show(string $id)
    {
        $role = $this->roleService->find($id);

        if (!$role) {
            return $this->error('Role not found.', 404);
        }

        return $this->success('Success', $role);
    }

    public function update(UpdateRoleRequest $request, string $id)
    {
        $role = $this->roleService->update($id, $request->validated());

        if (!$role) {
            return $this->error('Role not found.', 404);
        }

        return $this->success('Role updated successfully.', $role);
    }

    public function destroy(string $id)
    {
        $deleted = $this->roleService->delete($id);

        if (!$deleted) {
            return $this->error('Role not found or is a system role.', 400);
        }

        return $this->success('Role deleted successfully.');
    }

    public function syncPermissions(SyncPermissionsRequest $request, string $id)
    {
        $role = $this->roleService->syncPermissions($id, $request->validated()['permission_ids']);

        if (!$role) {
            return $this->error('Role not found.', 404);
        }

        return $this->success('Permissions synced successfully.', $role);
    }
}
