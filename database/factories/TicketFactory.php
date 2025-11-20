<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use App\Models\Invitation;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $invitation = Invitation::factory()->create();

        return [
            'code' => Str::random(8),
            'status' => $this->faker->randomElement([TicketStatus::UNUSED, TicketStatus::USED]),
            // used_at nullable
            // validated_by nullable
            'invitation_id' => $invitation->id,
        ];
    }
}
