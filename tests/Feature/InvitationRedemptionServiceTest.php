<?php

namespace Tests\Feature;

use App\Enums\InvitationStatus;
use App\Jobs\GenerateInvitationTickets;
use App\Models\Event;
use App\Models\Invitation;
use App\Models\Ticket;
use App\Services\ExternalInvitationService;
use App\Services\InvitationRedemptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('returns existing completed invitation without calling external service', function () {
    $hash = 'hash';
    $existingInvitation = Invitation::factory()->create([
        'external_id' => $hash,
        'status' => InvitationStatus::COMPLETED,
    ]);

    $this->mock(ExternalInvitationService::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('getInvitation');
    });

    $service = app(InvitationRedemptionService::class);
    $result = $service->redeem($hash);

    expect($result->id)->toBe($existingInvitation->id)
        ->and($result->external_id)->toBe($hash);
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

it('creates invitation and dispatches ticket generation job', function () {
    Queue::fake();

    $eventName = 'New Concert 2025';
    $hash = 'hash';
    $externalData = [
        'invitation_id' => $hash,
        'event_name' => $eventName,
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

    $event = Event::where('name', $eventName)->first();
    expect($event)->not->toBeNull();

    expect($result->event_id)->toBe($event->id)
        ->and($result->external_id)->toBe($hash)
        ->and($result->status)->toBe(InvitationStatus::PENDING)
        ->and($result->guest_count)->toBe(2);

    Queue::assertPushed(GenerateInvitationTickets::class, function ($job) use ($result) {
        return $job->invitation->id === $result->id;
    });
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

it('re-dispatches ticket generation for existing failed invitation', function () {
    Queue::fake();

    $hash = 'hash';
    $existingInvitation = Invitation::factory()->create([
        'external_id' => $hash,
        'status' => InvitationStatus::FAILED,
        'guest_count' => 5,
    ]);

    $this->mock(ExternalInvitationService::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('getInvitation');
    });

    $service = app(InvitationRedemptionService::class);
    $result = $service->redeem($hash);

    expect($result->id)->toBe($existingInvitation->id)
        ->and($result->status)->toBe(InvitationStatus::FAILED);

    Queue::assertPushed(GenerateInvitationTickets::class, function ($job) use ($existingInvitation) {
        return $job->invitation->id === $existingInvitation->id;
    });
});
