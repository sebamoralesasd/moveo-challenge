<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\TicketValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('raises an error if the ticket does not exist', function () {
    $code = 'INVALID';
    $service = app(TicketValidationService::class);

    expect(fn () => $service->validateTicket($code))
        ->toThrow(\Exception::class, "Ticket {$code} not found");
});
it('raises an error if the ticket is already used', function () {
    $ticket = Ticket::factory()->create(['status' => TicketStatus::USED]);
    $service = app(TicketValidationService::class);

    expect(fn () => $service->validateTicket($ticket->code))
        ->toThrow(\Exception::class, "Ticket {$ticket->code} was already used");
});

it('returns the updated ticket if it is unused', function () {
    $ticket = Ticket::factory()->create(['status' => TicketStatus::UNUSED]);
    $service = app(TicketValidationService::class);
    $result = $service->validateTicket($ticket->code);

    expect($result->id)->toBe($ticket->id);
    expect($result->status)->toBe(TicketStatus::USED);
});
