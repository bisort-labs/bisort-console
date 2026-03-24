<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Component;
use Tests\Support\FilamentTestable;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature')
;

afterEach(function (): void {
    Mockery::close();
});

if (!function_exists('livewire')) {
    /**
     * @param  class-string<Component>|Component|string|array<array-key, Component>  $component
     * @param  array<array-key, mixed>  $params
     */
    function livewire(string|array|object $component, array $params = []): FilamentTestable
    {
        return FilamentTestable::make($component, $params);
    }
}
