<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Services\TicketService;
use App\Services\TicketValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\User;
use Laravel\Passport\Passport;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('returns error when validation fails', function () {
    Passport::actingAs(User::factory()->create(['role' => UserRole::CHECKER->value]));
    $code = 'invalid-code';
    $errorMessage = "Ticket with code {$code} not found";

    $this->mock(TicketValidationService::class, function (MockInterface $mock) use ($code, $errorMessage) {
        $mock->shouldReceive('validateTicket')
            ->once()
            ->with($code)
            ->andThrow(new \Exception($errorMessage));
    });

    $response = $this->postJson("/api/tickets/{$code}");
    $response->assertStatus(422)
        ->assertJson([
            'error' => $errorMessage,
        ]);
});

it('validates ticket successfully', function () {
    Passport::actingAs(User::factory()->create(['role' => UserRole::CHECKER->value]));
    $code = 'valid-ticket-code';
    $ticket = Ticket::factory()->make([
        'code' => $code,
        'used_at' => now(),
    ]);

    $this->mock(TicketValidationService::class, function (MockInterface $mock) use ($code, $ticket) {
        $mock->shouldReceive('validateTicket')
            ->once()
            ->with($code)
            ->andReturn($ticket);
    });

    $response = $this->postJson("/api/tickets/{$code}");
    $response->assertStatus(201)
        ->assertJson([
            'status' => "Ticket {$code} was validated successfully.",
            'used_at' => $ticket->used_at->toISOString(),
        ]);
});

it('returns used tickets for an event', function () {
    Passport::actingAs(User::factory()->create(['role' => UserRole::ADMIN->value]));
    $eventId = 1;
    $ticket = Ticket::factory()->make();
    $paginator = new LengthAwarePaginator(collect([$ticket]), 1, 10);

    $this->mock(TicketService::class, function (MockInterface $mock) use ($eventId, $paginator) {
        $mock->shouldReceive('getUsedTicketsForEvent')
            ->once()
            ->with($eventId, 10, 1)
            ->andReturn($paginator);
    });

    $response = $this->getJson("/api/events/{$eventId}/tickets/used");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'current_page',
            'per_page',
            'total',
        ]);
});

it('returns used tickets for an event with custom page size', function () {
    Passport::actingAs(User::factory()->create(['role' => UserRole::ADMIN->value]));
    $eventId = 1;
    $perPage = 5;
    $ticket = Ticket::factory()->make();
    $paginator = new LengthAwarePaginator(collect([$ticket]), 1, $perPage);

    $this->mock(TicketService::class, function (MockInterface $mock) use ($eventId, $perPage, $paginator) {
        $mock->shouldReceive('getUsedTicketsForEvent')
            ->once()
            ->with($eventId, $perPage, 1)
            ->andReturn($paginator);
    });

    $response = $this->getJson("/api/events/{$eventId}/tickets/used?per_page={$perPage}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'current_page',
            'per_page',
            'total',
        ])
        ->assertJsonPath('per_page', $perPage);
});
