<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Services\TicketValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

uses(RefreshDatabase::class);

it('raises an error if the ticket does not exist', function () {
    $code = 'INVALID';

    $service = app(TicketValidationService::class);
    expect(fn () => $service->validateTicket($code))
        ->toThrow(ModelNotFoundException::class);
});

it('raises an error if the ticket is already used', function () {
    $ticket = Ticket::factory()->create(['status' => 'used']);
    $service = app(TicketValidationService::class);
    expect(fn () => $service->validateTicket($ticket->code))
        ->toThrow(\Exception::class, "Ticket {$ticket->code} was already used");
});

it('returns the updated ticket if it is unused', function () {
    $ticket = Ticket::factory()->create(['status' => 'unused']);
    $service = app(TicketValidationService::class);
    $result = $service->validateTicket($ticket->code);

    expect($result->id)->toBe($ticket->id);
    expect($result->status)->toBe('used');
});
