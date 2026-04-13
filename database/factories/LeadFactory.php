<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'company' => fake()->optional()->company(),
            'street' => fake()->optional()->streetAddress(),
            'city' => fake()->optional()->city(),
            'state' => fake()->optional()->word(),
            'zip' => fake()->optional()->postcode(),
            'country' => fake()->optional()->country(),
            'phone' => fake()->optional()->phoneNumber(),
            'source' => fake()->randomElement(LeadSource::cases()),
            'status' => fake()->randomElement(LeadStatus::cases()),
        ];
    }
}
