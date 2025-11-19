<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Services\TicketValidationService;
use Mockery\MockInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns error when validation fails', function () {
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
            'error' => $errorMessage
        ]);
});

it('validates ticket successfully', function () {
    $code = 'valid-ticket-code';
    $ticket = Ticket::factory()->make([
        'code' => $code,
        'used_at' => now()
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
            'used_at' => $ticket->used_at->toISOString()
        ]);
});
