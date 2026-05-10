<?php

namespace App\Services;

use App\Mail\SendOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Log the user in immediately after registration
        $token = auth('api')->login($user);

        return ['user' => $user, 'token' => $token];
    }

    public function login(array $credentials): ?string
    {
        // tymon/jwt-auth handles the attempt, returns token string or false
        return auth('api')->attempt($credentials);
    }

    public function me(): ?User
    {
        return auth('api')->user();
    }

    public function logout(): void
    {
        // Invalidates the current JWT token via tymon blacklist
        auth('api')->logout();
    }

    public function refresh(): ?string
    {
        // Returns a new token while blacklisting the old one
        return auth('api')->refresh();
    }

    public function changePassword(string $currentPassword, string $newPassword): bool
    {
        $user = auth('api')->user();

        // Verify current password before allowing change
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->update(['password' => Hash::make($newPassword)]);

        // Force re-login so old tokens are invalidated
        auth('api')->logout();

        return true;
    }

    public function requestPasswordResetOtp(string $email): ?string
    {
        $user = User::where('email', $email)->first();
        $otp = (string) random_int(100000, 999999);

        // updateOrCreate replaces any previous unverified OTP for this email
        PasswordResetOtp::updateOrCreate(
            ['email' => $email],
            [
                'otp_hash' => hash('sha256', $otp),
                'reset_token_hash' => null,
                'attempts' => 0,
                'verified_at' => null,
                'expires_at' => now()->addMinutes(10),
            ]
        );

        // Don't reveal whether the email exists — silently skip sending if no user
        if ($user && filled($user->email)) {
            Mail::to($user->email)->send(new SendOtpMail($otp, $user->name));
        }

        // Return the OTP so dev mode can expose it via debug_otp
        return $otp;
    }

    public function verifyPasswordResetOtp(string $email, string $otp): ?string
    {
        $reset = PasswordResetOtp::where('email', $email)->first();

        if (!$reset) {
            return null;
        }

        // Clean up expired records rather than leaving dead rows
        if ($reset->expires_at->isPast()) {
            $reset->delete();
            return null;
        }

        // Bruteforce protection — 5 wrong attempts locks it out
        if ($reset->attempts >= 5) {
            return null;
        }

        // Timing-safe comparison to prevent side-channel attacks
        if (!hash_equals($reset->otp_hash, hash('sha256', $otp))) {
            $reset->increment('attempts');
            return null;
        }

        // OTP is good — generate a new reset token for the next step
        $resetToken = Str::random(64);
        $reset->update([
            'verified_at' => now(),
            'reset_token_hash' => hash('sha256', $resetToken),
        ]);

        return $resetToken;
    }

    public function resetPasswordWithOtp(string $email, string $resetToken, string $password): bool
    {
        $reset = PasswordResetOtp::where('email', $email)->first();

        // Must have verified OTP first and still be within expiry
        if (!$reset || !$reset->verified_at || $reset->expires_at->isPast()) {
            return false;
        }

        // Validate the reset token matches what we issued
        if (!hash_equals((string) $reset->reset_token_hash, hash('sha256', $resetToken))) {
            return false;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        $user->update([
            'password' => Hash::make($password),
        ]);

        // One-time use — delete the record so it cannot be reused
        $reset->delete();

        return true;
    }
}
