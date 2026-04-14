<?php

declare(strict_types=1);

use App\Enums\CustomerType;
use App\Models\Customer;
use Tests\TestCase;

uses(TestCase::class);

it('casts customer attributes', function (): void {
    $customer = (new Customer)->setRawAttributes([
        'type' => CustomerType::B2B->value,
        'billing_address' => json_encode([
            'street' => 'Unter den Linden 1',
            'city' => 'Berlin',
        ], JSON_THROW_ON_ERROR),
        'is_vat_exempt' => 1,
    ]);

    expect($customer->type)->toBe(CustomerType::B2B)
        ->and($customer->billing_address)->toBe([
            'street' => 'Unter den Linden 1',
            'city' => 'Berlin',
        ])
        ->and($customer->is_vat_exempt)->toBeTrue()
    ;
});

it('provides labels and colors for customer types', function (): void {
    expect(CustomerType::B2B->getLabel())->toBe('B2B')
        ->and(CustomerType::B2B->getColor())->toBe('primary')
        ->and(CustomerType::B2C->getLabel())->toBe('B2C')
        ->and(CustomerType::B2C->getColor())->toBe('info')
    ;
});
