<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Ticket;
use App\Services\InvitationRedemptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('returns 404 if hash is missing', function () {
    $this->postJson('/api/invitations/')
        ->assertStatus(404);
});

it('redeems invitation successfully', function () {
    $hash = 'hash';
    $invitation = Invitation::factory()->create(['external_hash' => $hash]);

    $this->mock(InvitationRedemptionService::class, function (MockInterface $mock) use ($hash, $invitation) {
        $mock->shouldReceive('redeem')
            ->once()
            ->with($hash)
            ->andReturn($invitation);
    });

    $response = $this->postJson("/api/invitations/{$hash}");

    $response->assertStatus(201)
        ->assertJsonPath('data.id', $invitation->id)
        ->assertJsonStructure([
            'data' => [
                'id',
                'tickets',
                'external_hash'
            ]
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

    $response = $this->postJson("/api/invitations/{$hash}");

    $response->assertStatus(422)
        ->assertJson([
            'error' => $errorMessage
        ]);
});
