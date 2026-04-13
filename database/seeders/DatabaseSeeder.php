<?php

namespace Database\Seeders;

use App\Enums\ActionLogType;
use App\Enums\DealStage;
use App\Models\ClientProject;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $owners = new EloquentCollection([
            $defaultUser,
            ...User::factory(2)->create()->all(),
        ]);

        $projects = new EloquentCollection([
            ClientProject::query()->create([
                'name' => 'Northwind Website Refresh',
                'slug' => 'northwind-website-refresh',
                'description' => 'A redesign and rebuild of the public marketing site.',
                'is_active' => true,
            ]),
            ClientProject::query()->create([
                'name' => 'Helios Client Portal',
                'slug' => 'helios-client-portal',
                'description' => 'A customer portal rollout for support and billing workflows.',
                'is_active' => true,
            ]),
        ]);

        Lead::factory(8)->create()->each(function (Lead $lead) use ($owners, $projects): void {
            if (fake()->boolean(70)) {
                $lead->owner()->associate($owners->random());
                $lead->save();
            }

            Deal::factory(fake()->numberBetween(0, 3))
                ->for($lead)
                ->state([
                    'owner_id' => fake()->boolean(70) ? $owners->random()->getKey() : null,
                ])
                ->create()
                ->each(function (Deal $deal) use ($owners, $projects): void {
                    if ($deal->stage === DealStage::Won && fake()->boolean(60)) {
                        $deal->project()->associate($projects->random());
                        $deal->save();
                    }

                    $actionCount = fake()->numberBetween(0, 5);

                    for ($index = 0; $index < $actionCount; $index++) {
                        $type = fake()->randomElement(ActionLogType::cases());
                        $actorId = $type === ActionLogType::System
                            ? null
                            : $owners->random()->getKey();

                        $deal->actionLogs()->create([
                            'type' => $type,
                            'title' => fake()->randomElement([
                                'Discovery call',
                                'Proposal follow-up',
                                'Budget discussion',
                                'Scope alignment',
                                'Internal note',
                            ]),
                            'body' => fake()->optional()->sentence(),
                            'actor_id' => $actorId,
                            'happened_at' => Carbon::now()->subDays(fake()->numberBetween(0, 30))->subHours(fake()->numberBetween(0, 12)),
                        ]);
                    }
                })
            ;
        });
    }
}
