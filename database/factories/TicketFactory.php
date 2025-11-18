<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Ticket;
use App\Models\Invitation;

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
            'status' => $this->faker->randomElement(['unused', 'used']),
            // used_at nullable
            // validated_by nullable
            'invitation_id' => $invitation->id,
        ];
    }
}
