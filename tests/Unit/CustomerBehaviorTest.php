<?php

declare(strict_types=1);

use App\Enums\CustomerType;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Tests\TestCase;

uses(TestCase::class);

it('casts customer attributes and provides type labels and colors', function (): void {
    $customer = (new Customer)->setRawAttributes([
        'type' => CustomerType::B2C->value,
        'billing_address' => json_encode([
            'street' => 'Unter den Linden 1',
            'city' => 'Berlin',
            'state' => 'Berlin',
            'zip' => '10117',
            'country' => 'Germany',
        ], JSON_THROW_ON_ERROR),
        'is_vat_exempt' => 1,
    ]);

    expect($customer->type)->toBe(CustomerType::B2C)
        ->and($customer->billing_address)->toBe([
            'street' => 'Unter den Linden 1',
            'city' => 'Berlin',
            'state' => 'Berlin',
            'zip' => '10117',
            'country' => 'Germany',
        ])
        ->and($customer->is_vat_exempt)->toBeTrue()
        ->and(CustomerType::B2B->getLabel())->toBe('B2B')
        ->and(CustomerType::B2B->getColor())->toBe('primary')
        ->and(CustomerType::B2C->getLabel())->toBe('B2C')
        ->and(CustomerType::B2C->getColor())->toBe('info')
    ;
});

it('defines the expected customer action log relation', function (): void {
    $customer = new Customer;

    expect($customer->actionLogs())->toBeInstanceOf(MorphMany::class);
});
