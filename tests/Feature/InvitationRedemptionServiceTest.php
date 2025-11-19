<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Invitation;
use App\Models\Ticket;
use App\Services\ExternalInvitationService;
use App\Services\InvitationRedemptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('returns existing invitation without calling external service', function () {
    $hash = 'hash';
    $existingInvitation = Invitation::factory()->create([
        'external_hash' => $hash,
    ]);

    $this->mock(ExternalInvitationService::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('getInvitation');
    });

    $service = app(InvitationRedemptionService::class);
    $result = $service->redeem($hash);

    expect($result->id)->toBe($existingInvitation->id)
        ->and($result->external_hash)->toBe($hash);
});

it('throws exception and creates nothing when external service fails', function () {
    $hash = 'INVALIDHASH';
    $this->mock(ExternalInvitationService::class, function (MockInterface $mock) use ($hash) {
        $mock->shouldReceive('getInvitation')
            ->once()
            ->with($hash)
            ->andThrow(new \Exception('API Error'));
    });

    $service = app(InvitationRedemptionService::class);
    expect(fn () => $service->redeem($hash))
        ->toThrow(\Exception::class, 'API Error');

    expect(Invitation::count())->toBe(0)
        ->and(Ticket::count())->toBe(0)
        ->and(Event::count())->toBe(0);
});

it('creates invitation, tickets and new event successfully', function () {
    $hash = 'hash';
    $externalData = [
        'invitation_id' => $hash,
        'event_name' => 'New Concert 2025',
        'event_date' => '2025-12-31 20:00:00',
        'guest_count' => 2,
        'sector' => 'VIP',
    ];

    $this->mock(ExternalInvitationService::class, function (MockInterface $mock) use ($hash, $externalData) {
        $mock->shouldReceive('getInvitation')
            ->once()
            ->with($hash)
            ->andReturn($externalData);
    });

    $service = app(InvitationRedemptionService::class);
    $result = $service->redeem($hash);

    $event = Event::where('name', 'New Concert 2025')->first();
    expect($event)->not->toBeNull();

    expect($result->event_id)->toBe($event->id)
        ->and($result->external_hash)->toBe($hash)
        ->and($result->guest_count)->toBe(2);

    expect(Ticket::where('invitation_id', $result->id)->count())->toBe(2);
});

it('uses existing event if name matches', function () {
    $name = 'Existing Fest';
    $date = '2026-01-01 20:00:00';
    $existingEvent = Event::factory()->create([
        'name' => $name,
        'date' => $date,
    ]);
    expect(Event::count())->toBe(1);

    $hash = 'hash';
    $externalData = [
        'invitation_id' => $hash,
        'event_name' => $name,
        'event_date' => $date,
        'guest_count' => 1,
        'sector' => 'General',
    ];

    $this->mock(ExternalInvitationService::class, function (MockInterface $mock) use ($hash, $externalData) {
        $mock->shouldReceive('getInvitation')
            ->once()
            ->with($hash)
            ->andReturn($externalData);
    });

    $service = app(InvitationRedemptionService::class);
    $result = $service->redeem($hash);

    expect(Event::count())->toBe(1)
        ->and($result->event_id)->toBe($existingEvent->id);
});

it('rolls back invitation creation if ticket creation fails', function () {
    $hash = 'hash';
    $externalData = [
        'invitation_id' => $hash,
        'event_name' => 'Event',
        'event_date' => '2025-10-10 20:00:00',
        'guest_count' => 1,
        'sector' => 'General',
    ];

    $this->mock(ExternalInvitationService::class, function (MockInterface $mock) use ($hash, $externalData) {
        $mock->shouldReceive('getInvitation')
            ->once()
            ->with($hash)
            ->andReturn($externalData);
    });

    Ticket::creating(function ($ticket) {
        throw new \Exception('Database error during ticket creation');
    });

    $service = app(InvitationRedemptionService::class);

    try {
        $service->redeem($hash);
    } catch (\Exception $e) {
        // Expected exception
    }

    expect(Invitation::where('external_hash', $hash)->count())->toBe(0);
    expect(Event::count())->toBe(0);

    // Clean up model event listener to not affect other tests
    /* Ticket::flushEventListeners(); */
});
