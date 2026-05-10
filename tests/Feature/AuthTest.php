<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

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
