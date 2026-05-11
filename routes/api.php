<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:login');

    Route::post('auth/forgot-password', [AuthController::class, 'requestPasswordResetOtp'])
        ->middleware('throttle:forgot-password');

    Route::post('auth/verify-otp', [AuthController::class, 'verifyPasswordResetOtp'])
        ->middleware('throttle:otp');

    Route::post('auth/reset-password', [AuthController::class, 'resetPasswordWithOtp']);

    Route::middleware('auth:api')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::post('auth/me', [AuthController::class, 'me']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);

        Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.read');
        Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.create');
        Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.read');
        Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete');
        Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->middleware('permission:roles.update');

        Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.read');
        Route::get('permissions/grouped', [PermissionController::class, 'grouped'])->middleware('permission:permissions.read');
        Route::post('permissions', [PermissionController::class, 'store'])->middleware('permission:permissions.create');
        Route::get('permissions/{permission}', [PermissionController::class, 'show'])->middleware('permission:permissions.read');
        Route::put('permissions/{permission}', [PermissionController::class, 'update'])->middleware('permission:permissions.update');
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permissions.delete');

        Route::patch('users/{user}/role', [UserRoleController::class, 'update'])->middleware('permission:users.assign_role');
    });
});
