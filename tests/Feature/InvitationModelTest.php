<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Invitation;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Attribute validations
it('creates an Invitation with the factory and has valid attributes', function () {
    $invitation = Invitation::factory()->make();
    expect($invitation)->toBeInstanceOf(Invitation::class);
    expect($invitation->external_id)->not->toBeEmpty();
    expect($invitation->guest_count)->not->toBeEmpty();
    // TODO: validate enum
    expect($invitation->sector)->not->toBeEmpty();
});

it('persists an Invitation via the factory and exists in the database', function () {
    $invitation = Invitation::factory()->create();
    $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);
});

// Relationships
it('has tickets relation and can load related invitations', function () {
    $invitation = Invitation::factory()->create();
    // create at least one ticket for this event
    Ticket::factory()->create(['invitation_id' => $invitation->id]);

    $invitation->load('tickets');
    expect($invitation->tickets)->toHaveCount(1);
    expect($invitation->tickets->first())->toBeInstanceOf(Ticket::class);
});

it('belongs to an Event', function () {
    $event = Event::factory()->create();
    $invitation = Invitation::factory()->create(['event_id' => $event->id]);
    expect($invitation->event)->toBeInstanceOf(Event::class);
    expect($invitation->event->id)->toBe($event->id);
});

it('eager loads the related event', function () {
    $event = Event::factory()->create();
    $invitation = Invitation::factory()->create(['event_id' => $event->id]);
    $invWithEvent = Invitation::with('event')->find($invitation->id);
    expect($invWithEvent->event)->toBeInstanceOf(Event::class);
    expect($invWithEvent->event->id)->toBe($event->id);
});
