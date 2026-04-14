<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CustomerType;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    #[Override]
    public function definition(): array
    {
        $type = fake()->randomElement(CustomerType::cases());
        $isVatExempt = fake()->boolean(20);
        $countryCode = fake()->countryCode();
        $hasVatId = $type === CustomerType::B2B && ! $isVatExempt && fake()->boolean(75);

        return [
            'name' => $type === CustomerType::B2B ? fake()->company() : fake()->name(),
            'type' => $type,
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'country_code' => $countryCode,
            'vat_id' => $hasVatId ? sprintf('%s%s', $countryCode, fake()->numerify('#########')) : null,
            'tax_number' => fake()->optional()->numerify('TAX-########'),
            'is_vat_exempt' => $isVatExempt,
            'vat_exemption_reason' => $isVatExempt ? fake()->sentence() : null,
            'billing_address' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip' => fake()->postcode(),
                'country' => fake()->country(),
            ],
            'payment_terms_days' => fake()->optional(0.7)->randomElement([7, 14, 30, 45]),
        ];
    }
}
