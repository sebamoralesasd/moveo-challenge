<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Invitation;
use Illuminate\Database\Seeder;

class InvitationSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::firstOrCreate([
            'name' => 'Test API Concert',
            'date' => now()->addDays(30),
        ]);

        Invitation::firstOrCreate([
            'external_hash' => 'demo-hash-123',
        ], [
            'external_id' => 'demo-inv-1',
            'guest_count' => 2,
            'sector' => 'VIP',
            'event_id' => $event->id,
        ]);
    }
}
