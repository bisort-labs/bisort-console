<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ActionLogType;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Customer::factory()
            ->count(30)
            ->create()
            ->each(function (Customer $customer) use ($user): void {
                $actionCount = fake()->numberBetween(0, 5);

                for ($actionIndex = 0; $actionIndex < $actionCount; $actionIndex++) {
                    $type = fake()->randomElement([
                        ActionLogType::Note,
                        ActionLogType::System,
                    ]);

                    $customer->actionLogs()->create([
                        'type' => $type,
                        'title' => $type === ActionLogType::Note ? fake()->sentence(3) : fake()->randomElement([
                            'Customer created',
                            'Customer updated',
                            'Billing address updated',
                            'VAT ID updated',
                        ]),
                        'body' => fake()->optional()->sentence(),
                        'actor_id' => $type === ActionLogType::Note ? $user->getKey() : null,
                        'happened_at' => fake()->dateTimeBetween('-6 months'),
                    ]);
                }
            })
        ;
    }
}
