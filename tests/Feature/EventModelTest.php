<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Attribute validations
it('creates an Event with the factory and has valid attributes', function () {
    $event = Event::factory()->make();
    expect($event)->toBeInstanceOf(Event::class);
    expect($event->name)->not->toBeEmpty();
    // date should be a DateTime instance due to cast
    expect($event->date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('persists an Event via the factory and exists in the database', function () {
    $event = Event::factory()->create();
    $this->assertDatabaseHas('events', ['id' => $event->id]);
});

it('casts date to datetime when retrieved', function () {
    $event = Event::factory()->create(['date' => now()]);
    expect($event->date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

// Relationships
it('has invitations relation and can load related invitations', function () {
    $event = Event::factory()->create();
    // create at least one invitation for this event
    Invitation::factory()->create(['event_id' => $event->id]);

    $event->load('invitations');
    expect($event->invitations)->toHaveCount(1);
    expect($event->invitations->first())->toBeInstanceOf(Invitation::class);
});
