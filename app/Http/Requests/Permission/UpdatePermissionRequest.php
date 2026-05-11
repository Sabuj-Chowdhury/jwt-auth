<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission');

        return [
            'name' => "required|string|max:255|unique:permissions,name,{$permissionId},id",
            'slug' => "required|string|max:255|unique:permissions,slug,{$permissionId},id",
            'group' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
