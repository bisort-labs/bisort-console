<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;
use Safe\Exceptions\JsonException;
use function Safe\json_decode;

/**
 * @phpstan-type BillingAddress array{
 *     street: string|null,
 *     city: string|null,
 *     state: string|null,
 *     zip: string|null,
 *     country: string|null
 * }
 * @phpstan-type RawBillingAddress array{
 *     street?: bool|float|int|string|null,
 *     city?: bool|float|int|string|null,
 *     state?: bool|float|int|string|null,
 *     zip?: bool|float|int|string|null,
 *     country?: bool|float|int|string|null
 * }
 */
readonly class BillingAddressNormalizer
{
    private const array BILLING_ADDRESS_KEYS = [
        'street',
        'city',
        'state',
        'zip',
        'country',
    ];

    /**
     * @param  RawBillingAddress|bool|float|int|string|null  $value
     *
     * @return BillingAddress|null
     */
    public function normalize(array|bool|float|int|string|null $value): ?array
    {
        if (is_string($value) && ! filled($value)) {
            return null;
        }

        $value = is_string($value) ? $this->decodedValue($value) : $value;

        return is_array($value) ? $this->normalizedAddress($value) : null;
    }

    /**
     * @param  RawBillingAddress  $billingAddress
     *
     * @return BillingAddress
     */
    private function normalizedAddress(array $billingAddress): array
    {
        return [
            'street' => $this->normalizedPart($billingAddress['street'] ?? null),
            'city' => $this->normalizedPart($billingAddress['city'] ?? null),
            'state' => $this->normalizedPart($billingAddress['state'] ?? null),
            'zip' => $this->normalizedPart($billingAddress['zip'] ?? null),
            'country' => $this->normalizedPart($billingAddress['country'] ?? null),
        ];
    }

    private function normalizedPart(bool|float|int|string|null $value): ?string
    {
        return filled($value)
            ? Str::of(strval($value))->squish()->value()
            : null;
    }

    /**
     * @return RawBillingAddress|null
     */
    private function decodedValue(string $value): ?array
    {
        try {
            $decodedValue = json_decode($value, true);
        } catch (JsonException) {
            return null;
        }

        if (! is_array($decodedValue)) {
            return null;
        }

        return array_map(
            static fn ($part): bool|float|int|string|null => is_scalar($part) ? $part : null,
            array_replace(
                array_fill_keys(self::BILLING_ADDRESS_KEYS, null),
                array_intersect_key($decodedValue, array_flip(self::BILLING_ADDRESS_KEYS)),
            ),
        );
    }
}
