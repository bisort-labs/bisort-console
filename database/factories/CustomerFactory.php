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
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function definition(): array
    {
        $type = fake()->randomElement(CustomerType::cases());
        $isVatExempt = fake()->boolean(20);
        $countryCode = fake()->boolean(80) ? strtoupper(fake()->countryCode()) : null;
        $hasVatId = $type === CustomerType::B2B && ! $isVatExempt && $countryCode !== null && fake()->boolean(70);
        $billingAddress = fake()->boolean(80)
            ? [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->boolean(60) ? fake()->word() : null,
                'zip' => fake()->postcode(),
                'country' => fake()->country(),
            ]
            : null;

        return [
            'name' => $type === CustomerType::B2B ? fake()->company() : fake()->name(),
            'type' => $type,
            'email' => fake()->boolean(80) ? fake()->safeEmail() : null,
            'phone' => fake()->boolean(75) ? fake()->phoneNumber() : null,
            'country_code' => $countryCode,
            'vat_id' => $hasVatId ? sprintf('%s%s', $countryCode, fake()->numerify('#########')) : null,
            'tax_number' => fake()->boolean(60) ? fake()->numerify('TAX-########') : null,
            'is_vat_exempt' => $isVatExempt,
            'vat_exemption_reason' => $isVatExempt ? fake()->sentence() : null,
            'billing_address' => $billingAddress,
        ];
    }
}
