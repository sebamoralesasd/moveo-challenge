<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Attribute validations
it('creates a Ticket with the factory and has valid attributes', function () {
    $ticket = Ticket::factory()->make();
    expect($ticket)->toBeInstanceOf(Ticket::class);
    expect($ticket->code)->not->toBeEmpty();
    expect($ticket->status)->not->toBeEmpty();
});

it('persists a Ticket via the factory and exists in the database', function () {
    $ticket = Ticket::factory()->create();
    $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
});

it('creates tickets with unique codes', function () {
    $ticket_codes = Ticket::factory()->count(5)->create()->pluck('code')->toArray();

    expect($ticket_codes)->toHaveCount(5);
    expect(count($ticket_codes))->toBe(count(array_unique($ticket_codes)));
});

// Relationships
it('belongs to an Invitation', function () {
    $invitation = Invitation::factory()->create();
    $ticket = Ticket::factory()->create(['invitation_id' => $invitation->id]);
    expect($ticket->invitation)->toBeInstanceOf(Invitation::class);
    expect($ticket->invitation->id)->toBe($invitation->id);
});

it('eager loads the related invitation', function () {
    $invitation = Invitation::factory()->create();
    $ticket = Ticket::factory()->create(['invitation_id' => $invitation->id]);
    $ticketWithInvitation = Ticket::with('invitation')->find($ticket->id);
    expect($ticketWithInvitation->invitation)->toBeInstanceOf(Invitation::class);
    expect($ticketWithInvitation->invitation->id)->toBe($invitation->id);
});
