<?php

namespace App\Services;

use App\Enums\InvitationStatus;
use App\Jobs\GenerateInvitationTickets;
use App\Models\Event;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvitationRedemptionService
{
    public function __construct(protected ExternalInvitationService $external) {}

    public function redeem(string $hash): Invitation
    {
        $invitation = Invitation::where('external_id', $hash)->first();

        if ($invitation) {
            return $this->handleExistingInvitation($invitation);
        }

        Log::info("Invitation not found locally. Fetching from external API for hash: {$hash}");
        $data = $this->external->getInvitation($hash);

        return DB::transaction(function () use ($hash, $data) {
            Log::info('Starting DB transaction');

            $invitation = Invitation::where('external_id', $hash)->lockForUpdate()->first();

            if ($invitation) {
                return $this->handleExistingInvitation($invitation);
            }

            return $this->createNewInvitation($data);
        });
    }

    private function handleExistingInvitation(Invitation $invitation): Invitation
    {
        if ($invitation->status === InvitationStatus::FAILED) {
            Log::info("Invitation {$invitation->external_id} exists with status {$invitation->status->value}. Re-dispatching ticket generation.");
            GenerateInvitationTickets::dispatch($invitation);
        }

        return $invitation;
    }

    private function createNewInvitation(array $data): Invitation
    {
        $event = Event::firstOrCreate([
            'name' => $data['event_name'],
            'date' => $data['event_date'],
        ]);

        $invitation = Invitation::create([
            'external_id' => $data['invitation_id'],
            'guest_count' => $data['guest_count'],
            'sector' => $data['sector'],
            'event_id' => $event->id,
            'status' => InvitationStatus::PENDING,
        ]);

        GenerateInvitationTickets::dispatch($invitation);
        Log::info("Successfully created invitation for hash: {$invitation->external_id}. Tickets generation queued.");

        return $invitation;
    }
}
