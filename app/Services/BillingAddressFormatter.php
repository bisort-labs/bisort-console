<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

/**
 * @phpstan-type BillingAddress array{
 *     street: string|null,
 *     city: string|null,
 *     state: string|null,
 *     zip: string|null,
 *     country: string|null
 * }
 */
readonly class BillingAddressFormatter
{
    /**
     * @param  array<string, string|null>|null  $billingAddress
     */
    public function format(?array $billingAddress): string
    {
        $parts = $this->parts($billingAddress);

        return $parts === []
            ? Localization::translate('common.placeholder')
            : implode(', ', $parts);
    }

    /**
     * @param  array<string, string|null>|null  $billingAddress
     *
     * @return list<string>
     */
    private function parts(?array $billingAddress): array
    {
        if (! is_array($billingAddress)) {
            return [];
        }

        return array_values(array_filter([
            $this->normalizedPart($billingAddress['street'] ?? null),
            $this->normalizedPart($billingAddress['city'] ?? null),
            $this->normalizedPart($billingAddress['state'] ?? null),
            $this->normalizedPart($billingAddress['zip'] ?? null),
            $this->normalizedPart($billingAddress['country'] ?? null),
        ], static fn (?string $part): bool => filled($part)));
    }

    private function normalizedPart(bool|float|int|string|null $value): ?string
    {
        return filled($value)
            ? Str::of(strval($value))->squish()->value()
            : null;
    }
}
