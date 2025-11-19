<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Invitation;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvitationRedemptionService
{
    public function __construct(protected ExternalInvitationService $external) {}

    public function redeem(string $hash): Invitation
    {
        $invitation = Invitation::where('external_hash', $hash)->first();
        if ($invitation) {
            return $invitation;
        }

        Log::info("Invitation not found locally. Fetching from external API for hash: {$hash}");
        $data = $this->external->getInvitation($hash);

        return DB::transaction(function () use ($hash, $data) {
            $existing = Invitation::where('external_hash', $hash)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }
            $event = Event::firstOrCreate([
                'name' => $data['event_name'],
                'date' => $data['event_date'],
            ]);
            $invitation = Invitation::create([
                'external_hash' => $hash,
                'external_id' => $data['invitation_id'],
                'guest_count' => $data['guest_count'],
                'sector' => $data['sector'],
                'event_id' => $event->id,
            ]);

            // TODO: move to a background job
            for ($i = 0; $i < $invitation->guest_count; $i++) {
                Ticket::create([
                    'invitation_id' => $invitation->id,
                    'code' => Str::uuid(),
                ]);
            }

            Log::info("Successfully created invitation and tickets for hash: {$hash}");

            return $invitation;
        });
    }
}
