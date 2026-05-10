<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

// API versioning from the start — avoids breaking consumers later
Route::group(['prefix' => 'v1'], function () {
    // Public endpoints (no auth required)
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:login');

    // Password reset flow — rate limited to prevent abuse
    Route::post('auth/forgot-password', [AuthController::class, 'requestPasswordResetOtp'])
        ->middleware('throttle:forgot-password');

    Route::post('auth/verify-otp', [AuthController::class, 'verifyPasswordResetOtp'])
        ->middleware('throttle:otp');

    Route::post('auth/reset-password', [AuthController::class, 'resetPasswordWithOtp']);

    // Authenticated endpoints — JWT required
    Route::middleware('auth:api')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::post('auth/me', [AuthController::class, 'me']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);
    });
});
