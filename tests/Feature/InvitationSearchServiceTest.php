<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Invitation;
use App\Services\InvitationSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns all invitations when no filters provided', function () {
    Invitation::factory()->count(3)->create();

    $service = new InvitationSearchService;
    $results = $service->search([]);

    expect($results->items())->toHaveCount(3);
});

it('paginates results correctly', function () {
    Invitation::factory()->count(5)->create();

    $service = new InvitationSearchService;
    $results = $service->search([], 2);

    expect($results->perPage())->toBe(2);
    expect($results->items())->toHaveCount(2);
    expect($results->total())->toBe(5);
});

it('respects page parameter', function () {
    $invitations = Invitation::factory()->count(5)->create()->sortByDesc('created_at')->values();

    $service = new InvitationSearchService;
    // Get second page with 2 items per page (items 3 and 4)
    $results = $service->search([], 2, 2);

    expect($results->currentPage())->toBe(2);
    expect($results->items())->toHaveCount(2);
    expect($results->items()[0]->id)->toBe($invitations[2]->id);
});

it('filters invitations by event', function () {
    $event1 = Event::factory()->create();
    $event2 = Event::factory()->create();

    $invitation1 = Invitation::factory()->create(['event_id' => $event1->id]);
    Invitation::factory()->create(['event_id' => $event2->id]);

    $service = new InvitationSearchService;
    $results = $service->search(['event_id' => $event1->id]);

    expect($results->items())->toHaveCount(1);
    expect($results->first()->id)->toBe($invitation1->id);
});

it('filters invitations by sector', function () {
    Invitation::factory()->create(['sector' => 'VIP']);
    $invitationTarget = Invitation::factory()->create(['sector' => 'General']);

    $service = new InvitationSearchService;
    $results = $service->search(['sector' => 'General']);

    expect($results->items())->toHaveCount(1);
    expect($results->first()->id)->toBe($invitationTarget->id);
});

it('filters invitations by date range', function () {
    $targetDate = now()->subDays(5);
    $oldDate = now()->subDays(10);
    $futureDate = now()->addDays(5);

    $targetInvitation = Invitation::factory()->create(['created_at' => $targetDate]);
    Invitation::factory()->create(['created_at' => $oldDate]);
    Invitation::factory()->create(['created_at' => $futureDate]);

    $service = new InvitationSearchService;
    $results = $service->search([
        'date_from' => $targetDate->subDay()->toDateString(),
        'date_to' => $targetDate->addDay()->toDateString(),
    ]);

    expect($results->items())->toHaveCount(1);
    expect($results->first()->id)->toBe($targetInvitation->id);
});
