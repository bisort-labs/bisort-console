<?php

declare(strict_types=1);

use App\Models\ClientProject;
use App\Models\User;
use Filament\Panel;

it('casts the client project active flag to a boolean', function (): void {
    $project = (new ClientProject)->setRawAttributes([
        'is_active' => 1,
    ]);

    expect($project->is_active)->toBeTrue();

    $project->is_active = false;

    expect($project->is_active)->toBeFalse();
});

it('allows users to access only the console panel', function (): void {
    $user = new User;

    $consolePanel = new class('console') extends Panel
    {
        public function __construct(
            private readonly string $panelId,
        ) {}

        #[Override]
        public function getId(): string
        {
            return $this->panelId;
        }
    };

    $otherPanel = new class('admin') extends Panel
    {
        public function __construct(
            private readonly string $panelId,
        ) {}

        #[Override]
        public function getId(): string
        {
            return $this->panelId;
        }
    };

    expect($user->canAccessPanel($consolePanel))->toBeTrue()
        ->and($user->canAccessPanel($otherPanel))->toBeFalse()
    ;
});
