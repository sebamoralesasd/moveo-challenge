<?php

namespace Database\Factories;

use App\Enums\InvitationStatus;
use App\Models\Event;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitation>
 */
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $event = Event::factory()->create();

        return [
            'external_id' => Str::random(8),
            'guest_count' => $this->faker->numberBetween(1, 10),
            'sector' => $this->faker->randomElement(['General', 'VIP', 'Platea']),
            'event_id' => $event->id,
            'status' => InvitationStatus::PENDING->value,
        ];

    }
}
