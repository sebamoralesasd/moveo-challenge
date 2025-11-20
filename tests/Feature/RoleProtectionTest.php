<?php

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

test('admin can access invitations', function () {
    $user = User::factory()->create(['role' => 'admin']);
    Passport::actingAs($user);

    $this->getJson('/api/invitations')
        ->assertStatus(200);
});

test('checker cannot access invitations', function () {
    $user = User::factory()->create(['role' => 'checker']);
    Passport::actingAs($user);

    $this->getJson('/api/invitations')
        ->assertStatus(403);
});

test('checker can validate tickets', function () {
    $user = User::factory()->create(['role' => 'checker']);
    Passport::actingAs($user);

    $ticket = Ticket::factory()->create(['status' => 'unused']);

    $this->postJson("/api/tickets/{$ticket->code}")
        ->assertStatus(201);
});

test('admin cannot validate tickets', function () {
    $user = User::factory()->create(['role' => 'admin']);
    Passport::actingAs($user);

    $ticket = Ticket::factory()->create();

    $this->postJson("/api/tickets/{$ticket->code}")
        ->assertStatus(403);
});
