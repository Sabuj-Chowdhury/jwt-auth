<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AuthService;

class AuthController extends Controller
{
    use ApiResponse;

    // Keeping controllers thin — all business logic lives in AuthService
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(UserRegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return $this->respondWithToken($result['token']);
    }

    public function login(LoginRequest $request)
    {
        $token = $this->authService->login($request->validated());

        if (!$token) {
            return $this->error('Invalid credentials', 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        $user = $this->authService->me();

        if (!$user) {
            return $this->error('Unauthorized', 401);
        }

        return $this->success('Success', $user);
    }

    public function logout()
    {
        $this->authService->logout();

        return $this->success('Successfully logged out');
    }

    public function refresh()
    {
        $token = $this->authService->refresh();

        if (!$token) {
            return $this->error('Could not refresh token', 401);
        }

        return $this->respondWithToken($token);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $success = $this->authService->changePassword(
            $request->validated()['current_password'],
            $request->validated()['new_password']
        );

        if (!$success) {
            return $this->error('Current password is incorrect', 400);
        }

        return $this->success('Password changed successfully. Please login again.');
    }

    public function requestPasswordResetOtp(ForgotPasswordRequest $request)
    {
        $otp = $this->authService->requestPasswordResetOtp($request->validated()['email']);

        $response = [
            'message' => 'If the email exists, an OTP was sent.',
        ];

        // Handy during development — returns the OTP so you don't need an email client
        if (config('app.debug')) {
            $response['debug_otp'] = $otp;
        }

        return $this->success($response['message'], $response);
    }

    public function verifyPasswordResetOtp(VerifyOtpRequest $request)
    {
        $resetToken = $this->authService->verifyPasswordResetOtp(
            $request->validated()['email'],
            $request->validated()['otp']
        );

        if (!$resetToken) {
            return $this->error('Invalid or expired OTP.', 422);
        }

        return $this->success('OTP verified successfully.', [
            'reset_token' => $resetToken,
        ]);
    }

    public function resetPasswordWithOtp(ResetPasswordRequest $request)
    {
        $success = $this->authService->resetPasswordWithOtp(
            $request->validated()['email'],
            $request->validated()['reset_token'],
            $request->validated()['password']
        );

        if (!$success) {
            return $this->error('Verification is missing or expired.', 422);
        }

        return $this->success('Password updated successfully.');
    }
}
