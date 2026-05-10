<?php

use App\Mail\SendOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

it('can register a new user', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['access_token', 'token_type', 'expires_in'],
        ]);

    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
});

it('can login with valid credentials', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['access_token', 'token_type', 'expires_in'],
        ]);
});

it('cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
        ]);
});

it('can get current user with valid token', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Success',
        ])
        ->assertJsonPath('data.email', $user->email);
});

it('can logout', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
});

it('does not reveal if email exists on forgot password', function () {
    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('message', 'If the email exists, an OTP was sent.');
});

it('sends otp email for existing user', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'password' => Hash::make('password'),
    ]);

    $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'john@example.com',
    ]);

    Mail::assertSent(SendOtpMail::class);
});

it('can verify otp and get reset token', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
    ]);

    PasswordResetOtp::create([
        'email' => 'john@example.com',
        'otp_hash' => hash('sha256', '123456'),
        'attempts' => 0,
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/v1/auth/verify-otp', [
        'email' => 'john@example.com',
        'otp' => '123456',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['reset_token'],
        ]);
});

it('fails with invalid otp', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('password'),
    ]);

    PasswordResetOtp::create([
        'email' => 'john@example.com',
        'otp_hash' => hash('sha256', '123456'),
        'attempts' => 0,
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/v1/auth/verify-otp', [
        'email' => 'john@example.com',
        'otp' => '000000',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid or expired OTP.',
        ]);
});

it('can reset password with valid reset token', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $resetToken = 'valid-reset-token-1234567890-abc';

    PasswordResetOtp::create([
        'email' => 'john@example.com',
        'otp_hash' => hash('sha256', '123456'),
        'reset_token_hash' => hash('sha256', $resetToken),
        'attempts' => 0,
        'verified_at' => now(),
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'john@example.com',
        'reset_token' => $resetToken,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

it('fails reset password without verified otp', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = 'abcdef1234567890abcdef1234567890abcdef12';

    PasswordResetOtp::create([
        'email' => 'john@example.com',
        'otp_hash' => hash('sha256', '123456'),
        'reset_token_hash' => hash('sha256', $token),
        'attempts' => 0,
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'john@example.com',
        'reset_token' => $token,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Verification is missing or expired.',
        ]);
});

it('requires authentication for me endpoint', function () {
    $response = $this->postJson('/api/v1/auth/me');

    $response->assertStatus(401);
});

it('validates registration fields', function () {
    $response = $this->postJson('/api/v1/auth/register', []);

    $response->assertStatus(422);
});

it('validates login fields', function () {
    $response = $this->postJson('/api/v1/auth/login', []);

    $response->assertStatus(422);
});

it('validates change password fields', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/v1/auth/change-password', []);

    $response->assertStatus(422);
});

it('can change password', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => Hash::make('current-password'),
    ]);

    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'current-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Password changed successfully. Please login again.',
        ]);

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

it('fails change password with wrong current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'wrong-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Current password is incorrect',
        ]);
});
