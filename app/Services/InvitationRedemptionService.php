<?php

namespace App\Services;

use App\Jobs\GenerateInvitationTickets;
use App\Models\Event;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvitationRedemptionService
{
    public function __construct(protected ExternalInvitationService $external)
    {
    }

    public function redeem(string $hash): Invitation
    {
        $invitation = Invitation::where('external_id', $hash)->first();
        if ($invitation) {
            return $invitation;
        }

        Log::info("Invitation not found locally. Fetching from external API for hash: {$hash}");
        $data = $this->external->getInvitation($hash);

        return DB::transaction(function () use ($hash, $data) {
            Log::info("Starting DB transaction");
            $existing = Invitation::where('external_id', $hash)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }
            $event = Event::firstOrCreate([
                'name' => $data['event_name'],
                'date' => $data['event_date'],
            ]);
            $invitation = Invitation::create([
                'external_id' => $data['invitation_id'],
                'guest_count' => $data['guest_count'],
                'sector' => $data['sector'],
                'event_id' => $event->id,
            ]);

            GenerateInvitationTickets::dispatch($invitation);
            Log::info("Successfully created invitation for hash: {$hash}. Tickets generation queued.");

            return $invitation;
        });
    }
}
