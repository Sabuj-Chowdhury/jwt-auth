<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AssignRoleRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;

class UserRoleController extends Controller
{
    use ApiResponse;

    public function update(AssignRoleRequest $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('User not found.', 404);
        }

        $user->update([
            'role_id' => $request->validated()['role_id'],
        ]);

        return $this->success('Role assigned successfully.', $user->load('role'));
    }
}
