<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

test('admin can access invitations', function () {
    $user = User::factory()->create(['role' => UserRole::ADMIN->value]);
    Passport::actingAs($user);

    $this->getJson('/api/invitations')
        ->assertStatus(200);
});

test('checker cannot access invitations', function () {
    $user = User::factory()->create(['role' => UserRole::CHECKER->value]);
    Passport::actingAs($user);

    $this->getJson('/api/invitations')
        ->assertStatus(403);
});

test('checker can validate tickets', function () {
    $user = User::factory()->create(['role' => UserRole::CHECKER->value]);
    Passport::actingAs($user);

    $ticket = Ticket::factory()->create(['status' => TicketStatus::UNUSED]);

    $this->postJson("/api/tickets/{$ticket->code}")
        ->assertStatus(201);
});

test('admin cannot validate tickets', function () {
    $user = User::factory()->create(['role' => UserRole::ADMIN->value]);
    Passport::actingAs($user);

    $ticket = Ticket::factory()->create();

    $this->postJson("/api/tickets/{$ticket->code}")
        ->assertStatus(403);
});
