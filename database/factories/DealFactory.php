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
        return [
            'lead_id' => Lead::factory(),
            'project_id' => null,
            'title' => fake()->sentence(3),
            'stage' => fake()->randomElement(DealStage::cases()),
            'expected_value_cents' => fake()->numberBetween(0, 5000000),
            'currency' => 'EUR',
            'probability' => fn (array $attributes): ?int => match ($this->normalizeStage($attributes['stage'] ?? null)) {
                DealStage::Won, DealStage::Lost => null,
                default => fake()->numberBetween(5, 95),
            },
            'close_date' => fake()->optional()->dateTimeBetween('now', '+6 months'),
            'lost_reason' => fn (array $attributes): ?string => $this->normalizeStage($attributes['stage'] ?? null) === DealStage::Lost
                ? fake()->sentence()
                : null,
            'notes' => fake()->optional()->paragraph(),
            'owner_id' => fake()->boolean(40) ? User::factory() : null,
        ];
    }

    private function normalizeStage(mixed $stage): ?DealStage
    {
        if ($stage instanceof DealStage) {
            return $stage;
        }

        return is_string($stage) ? DealStage::tryFrom($stage) : null;
    }
}
