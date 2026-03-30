<?php

declare(strict_types=1);

namespace Tests\Support;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Traversable;

final readonly class FilamentTestable
{
    /**
     * @param  Testable<Component>  $testable
     */
    public function __construct(
        private Testable $testable,
    ) {}

    /**
     * @param  class-string<Component>|Component|string|array<array-key, Component>  $component
     * @param  array<array-key, mixed>  $params
     */
    public static function make(string|array|object $component, array $params = []): self
    {
        return new self(Livewire::test($component, $params));
    }

    /**
     * @param  array<array-key, mixed>|Closure  $state
     */
    public function fillForm(array|Closure $state = [], ?string $form = null): self
    {
        $this->testable->__call('fillForm', [$state, $form]);

        return $this;
    }

    /**
     * @param  array<array-key, mixed>  $keys
     */
    public function assertHasFormErrors(array $keys = [], ?string $form = null): self
    {
        $this->testable->__call('assertHasFormErrors', [$keys, $form]);

        return $this;
    }

    /**
     * @param  array<array-key, mixed>  $keys
     */
    public function assertHasNoFormErrors(array $keys = [], ?string $form = null): self
    {
        $this->testable->__call('assertHasNoFormErrors', [$keys, $form]);

        return $this;
    }

    /**
     * @param  array<array-key, mixed>|Closure  $state
     */
    public function assertFormSet(array|Closure $state, string $form = 'form'): self
    {
        $this->testable->__call('assertFormSet', [$state, $form]);

        return $this;
    }

    /**
     * @param  array<array-key, mixed>|Traversable<array-key, mixed>  $records
     */
    public function assertCanSeeTableRecords(array|Traversable $records, bool $inOrder = false): self
    {
        $this->testable->__call('assertCanSeeTableRecords', [
            is_array($records) || $records instanceof Collection ? $records : iterator_to_array($records),
            $inOrder,
        ]);

        return $this;
    }

    public function assertNotified(Notification|string|null $notification = null): self
    {
        $this->testable->__call('assertNotified', [$notification]);

        return $this;
    }

    public function assertHasNoErrors(): self
    {
        $this->testable->assertHasNoErrors();

        return $this;
    }

    public function call(string $method, mixed ...$params): self
    {
        $this->testable->call($method, ...$params);

        return $this;
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @param  array<array-key, mixed>  $arguments
     */
    public function callAction(string $name, array $data = [], array $arguments = []): self
    {
        $this->testable->__call('callAction', [$name, $data, $arguments]);

        return $this;
    }

    public function assertRedirect(?string $uri = null): self
    {
        $this->testable->assertRedirect($uri);

        return $this;
    }
}
