<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Invitation;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns used tickets for a specific event', function () {
    $event = Event::factory()->create();
    $otherEvent = Event::factory()->create();

    $invitation = Invitation::factory()->create(['event_id' => $event->id]);
    $otherInvitation = Invitation::factory()->create(['event_id' => $otherEvent->id]);

    $usedTicket = Ticket::factory()->create([
        'invitation_id' => $invitation->id,
        'status' => 'used',
        'used_at' => now(),
    ]);

    Ticket::factory()->create([
        'invitation_id' => $invitation->id,
        'status' => 'unused',
        'used_at' => null,
    ]);

    Ticket::factory()->create([
        'invitation_id' => $otherInvitation->id,
        'status' => 'used',
        'used_at' => now(),
    ]);

    $service = new TicketService;
    $results = $service->getUsedTicketsForEvent($event->id);

    expect($results->items())->toHaveCount(1);
    expect($results->first()->id)->toBe($usedTicket->id);
});

it('respects page size parameter', function () {
    $event = Event::factory()->create();
    $invitation = Invitation::factory()->create(['event_id' => $event->id]);

    Ticket::factory()->count(5)->create([
        'invitation_id' => $invitation->id,
        'status' => 'used',
        'used_at' => now(),
    ]);

    $service = new TicketService;
    $results = $service->getUsedTicketsForEvent($event->id, 2);

    expect($results->perPage())->toBe(2);
    expect($results->items())->toHaveCount(2);
});

it('respects page parameter', function () {
    $event = Event::factory()->create();
    $invitation = Invitation::factory()->create(['event_id' => $event->id]);

    $tickets = Ticket::factory()->count(5)->create([
        'invitation_id' => $invitation->id,
        'status' => 'used',
        'used_at' => now(),
    ]);

    $service = new TicketService;
    // Get second page with 2 items per page (items 3 and 4)
    $results = $service->getUsedTicketsForEvent($event->id, 2, 2);

    expect($results->currentPage())->toBe(2);
    expect($results->items())->toHaveCount(2);
    expect($results->items()[0]->id)->toBe($tickets[2]->id);
});
