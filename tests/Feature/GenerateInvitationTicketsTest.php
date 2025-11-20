<?php

namespace Tests\Feature;

use App\Enums\InvitationStatus;
use App\Jobs\GenerateInvitationTickets;
use App\Models\Invitation;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('generates correct number of tickets for invitation', function () {
    $invitation = Invitation::factory()->create(['guest_count' => 3]);

    expect(Ticket::count())->toBe(0);

    $job = new GenerateInvitationTickets($invitation);
    $job->handle();

    expect(Ticket::count())->toBe(3);
});

it('updates invitation status as completed', function () {
    $invitation = Invitation::factory()->create(['guest_count' => 3]);

    $job = new GenerateInvitationTickets($invitation);
    $job->handle();

    expect($invitation->status)->toBe(InvitationStatus::COMPLETED);
});

it('creates tickets with correct invitation_id', function () {
    $invitation = Invitation::factory()->create(['guest_count' => 2]);

    $job = new GenerateInvitationTickets($invitation);
    $job->handle();

    $tickets = Ticket::where('invitation_id', $invitation->id)->get();
    expect($tickets)->toHaveCount(2);
    expect($tickets->every(fn ($ticket) => $ticket->invitation_id === $invitation->id))->toBeTrue();
});

it('does not create duplicate tickets if run multiple times', function () {
    $invitation = Invitation::factory()->create(['guest_count' => 2]);

    $job1 = new GenerateInvitationTickets($invitation);
    $job1->handle();

    expect(Ticket::count())->toBe(2);

    $job2 = new GenerateInvitationTickets($invitation);
    $job2->handle();

    expect(Ticket::count())->toBe(2);
});

it('handles zero guest count', function () {
    $invitation = Invitation::factory()->create(['guest_count' => 0]);

    $job = new GenerateInvitationTickets($invitation);
    $job->handle();

    expect(Ticket::count())->toBe(0);
});

it('logs error on failure', function () {
    Log::shouldReceive('error')
        ->once()
        ->with(\Mockery::pattern('/Failed to generate tickets for .*/'));

    $invitation = Invitation::factory()->create(['guest_count' => 1]);
    $job = new GenerateInvitationTickets($invitation);

    $exception = new \Exception('Database error');
    $job->failed($exception);
});
