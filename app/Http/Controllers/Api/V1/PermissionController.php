<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Http\Responses\ApiResponse;
use App\Services\PermissionService;

class PermissionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PermissionService $permissionService
    ) {}

    public function index()
    {
        return $this->success('Success', $this->permissionService->all());
    }

    public function grouped()
    {
        return $this->success('Success', $this->permissionService->grouped());
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = $this->permissionService->create($request->validated());

        return $this->success('Permission created successfully.', $permission, 201);
    }

    public function show(string $id)
    {
        $permission = $this->permissionService->find($id);

        if (!$permission) {
            return $this->error('Permission not found.', 404);
        }

        return $this->success('Success', $permission);
    }

    public function update(UpdatePermissionRequest $request, string $id)
    {
        $permission = $this->permissionService->update($id, $request->validated());

        if (!$permission) {
            return $this->error('Permission not found.', 404);
        }

        return $this->success('Permission updated successfully.', $permission);
    }

    public function destroy(string $id)
    {
        $deleted = $this->permissionService->delete($id);

        if (!$deleted) {
            return $this->error('Permission not found.', 404);
        }

        return $this->success('Permission deleted successfully.');
    }
}
