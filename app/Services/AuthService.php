<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = auth('api')->login($user);

        return ['user' => $user, 'token' => $token];
    }

    public function login(array $credentials): ?string
    {
        return auth('api')->attempt($credentials);
    }

    public function me(): ?User
    {
        return auth('api')->user();
    }

    public function logout(): void
    {
        auth('api')->logout();
    }

    public function refresh(): ?string
    {
        return auth('api')->refresh();
    }

    public function changePassword(string $currentPassword, string $newPassword): bool
    {
        $user = auth('api')->user();

        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->update(['password' => Hash::make($newPassword)]);

        auth('api')->logout();

        return true;
    }
}
