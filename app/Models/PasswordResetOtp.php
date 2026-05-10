<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Single table for the entire password reset flow — OTP verification + reset token
class PasswordResetOtp extends Model
{
    protected $fillable = [
        'email',
        'otp_hash',
        'reset_token_hash',
        'attempts',
        'verified_at',
        'expires_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
