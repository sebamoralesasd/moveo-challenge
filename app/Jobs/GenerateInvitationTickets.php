<?php

namespace App\Jobs;

use App\Models\Invitation;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GenerateInvitationTickets implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Invitation $invitation
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $guest_count = $this->invitation->guest_count;

        Log::info("Generating {$guest_count} tickets for invitation {$this->invitation->external_id}");

        // Bulk insert (one SQL query instead of N queries)
        $tickets = [];
        for ($i = 0; $i < $guest_count; $i++) {
            $tickets[] = [
                'invitation_id' => $this->invitation->id,
                'code' => Str::uuid(),
                'status' => 'unused',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Ticket::insert($tickets);
        Log::info("{$guest_count} tickets for invitation {$this->invitation->external_id} generated successfully.");
    }

    public function failed(Throwable $ex)
    {
        Log::error("Failed to generate tickets for {$this->invitation->external_id}: " . $ex->getMessage());
        // TODO: update invitation as failed?
    }
}
