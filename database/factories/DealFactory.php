<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DealStage;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
{
    protected $model = Deal::class;

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function definition(): array
    {
        $stage = fake()->randomElement(DealStage::cases());

        return [
            'lead_id' => Lead::factory(),
            'project_id' => null,
            'title' => fake()->sentence(3),
            'stage' => $stage,
            'expected_value_cents' => fake()->numberBetween(0, 5000000),
            'currency' => 'EUR',
            'probability' => $stage === DealStage::Won || $stage === DealStage::Lost ? null : fake()->numberBetween(5, 95),
            'close_date' => fake()->optional()->dateTimeBetween('now', '+6 months'),
            'lost_reason' => $stage === DealStage::Lost ? fake()->sentence() : null,
            'notes' => fake()->optional()->paragraph(),
            'owner_id' => fake()->boolean(40) ? User::factory() : null,
        ];
    }
}
