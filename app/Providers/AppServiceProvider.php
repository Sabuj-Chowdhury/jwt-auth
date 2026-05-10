<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Login: 5 attempts per minute per email+IP combo
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
        });

        // Forgot password: 3 requests per minute — don't let people spam the endpoint
        RateLimiter::for('forgot-password', function (Request $request) {
            return Limit::perMinute(3)->by($request->input('email') . '|' . $request->ip());
        });

        // OTP verification: 5 attempts per minute
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
        });
    }
}
