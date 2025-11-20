<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Services\InvitationRedemptionService;
use App\Services\InvitationSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use App\Models\User;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

// GET /invitations
it('returns invitations list', function () {
    Passport::actingAs(User::factory()->create(['role' => 'admin']));
    $invitations = Invitation::factory()->count(3)->make();
    $paginator = new LengthAwarePaginator($invitations, 3, 10);

    $this->mock(InvitationSearchService::class, function (MockInterface $mock) use ($paginator) {
        $mock->shouldReceive('search')
            ->once()
            ->with(
                [], // Empty filters
                10  // Default perPage
            )
            ->andReturn($paginator);
    });

    $response = $this->getJson('/api/invitations');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'current_page',
            'per_page',
            'total',
        ]);
});

it('passes filters to search service', function () {
    Passport::actingAs(User::factory()->create(['role' => 'admin']));
    $invitations = Invitation::factory()->count(1)->make();
    $paginator = new LengthAwarePaginator($invitations, 1, 10);
    $eventId = 123;
    $sector = 'VIP';

    $this->mock(InvitationSearchService::class, function (MockInterface $mock) use ($paginator, $eventId, $sector) {
        $mock->shouldReceive('search')
            ->once()
            ->with(
                \Mockery::subset([
                    'event_id' => (string) $eventId,
                    'sector' => $sector,
                ]),
                10 // Default is 10
            )
            ->andReturn($paginator);
    });

    $response = $this->getJson("/api/invitations?event_id={$eventId}&sector={$sector}");
    $response->assertStatus(200);
});


// POST /invitations/{hash}/redeem
it('redeems invitation successfully', function () {
    $hash = 'hash';
    $invitation = Invitation::factory()->create(['external_id' => $hash]);

    $this->mock(InvitationRedemptionService::class, function (MockInterface $mock) use ($hash, $invitation) {
        $mock->shouldReceive('redeem')
            ->once()
            ->with($hash)
            ->andReturn($invitation);
    });

    $response = $this->postJson("/api/invitations/{$hash}/redeem");

    $response->assertStatus(201)
        ->assertJsonPath('data.id', $invitation->id)
        ->assertJsonStructure([
            'data' => [
                'id',
                'tickets',
                'external_id',
            ],
        ]);
});

it('returns an error when service throws exception', function () {
    $hash = 'invalid-hash';
    $errorMessage = 'API error';

    // Mock the Service to throw
    $this->mock(InvitationRedemptionService::class, function (MockInterface $mock) use ($hash, $errorMessage) {
        $mock->shouldReceive('redeem')
            ->once()
            ->with($hash)
            ->andThrow(new \Exception($errorMessage));
    });

    $response = $this->postJson("/api/invitations/{$hash}/redeem");

    $response->assertStatus(422)
        ->assertJson([
            'error' => $errorMessage,
        ]);
});
