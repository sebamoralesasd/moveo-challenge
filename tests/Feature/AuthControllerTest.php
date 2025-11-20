<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;

/* use function Pest\Laravel\{postJson}; */

uses(RefreshDatabase::class);

beforeEach(function () {
    $clientRepository = new ClientRepository();
    $clientRepository->createPersonalAccessGrantClient('Test Personal Access Client');
});

test('user can register', function () {
    $data = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'role' => 'admin',
    ];
    $response = $this->postJson('/api/register', $data);
    $response->assertStatus(201)
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'role'],
            'token',
        ]);
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'admin',
    ]);
});

test('user can login', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);
    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    $response->assertStatus(200)
        ->assertJsonStructure([
            'user',
            'token',
        ]);
});
