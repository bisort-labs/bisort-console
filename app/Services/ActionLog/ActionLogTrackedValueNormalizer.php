<?php

declare(strict_types=1);

namespace App\Services\ActionLog;

use App\Services\BillingAddressNormalizer;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @phpstan-type BillingAddress array{
 *     street: string|null,
 *     city: string|null,
 *     state: string|null,
 *     zip: string|null,
 *     country: string|null
 * }
 */
readonly class ActionLogTrackedValueNormalizer
{
    private const array BILLING_ADDRESS_KEYS = [
        'street',
        'city',
        'state',
        'zip',
        'country',
    ];

    public function __construct(
        private BillingAddressNormalizer $billingAddressNormalizer,
    ) {
    }

    /**
     * @return BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null
     */
    public function normalize(
        Model $actionable,
        string $field,
        bool $original,
    ): array|BackedEnum|DateTimeInterface|float|int|string|bool|null {
        if ($field === 'billing_address') {
            return $this->normalizedBillingAddress($actionable, $original);
        }

        $value = $original
            ? $actionable->getOriginal($field)
            : $actionable->getAttribute($field);

        return match (true) {
            $value instanceof BackedEnum,
            $value instanceof DateTimeInterface,
            is_scalar($value) => $value,
            default => null,
        };
    }

    /**
     * @return BillingAddress|null
     */
    private function normalizedBillingAddress(Model $actionable, bool $original): ?array
    {
        $value = $original
            ? $actionable->getOriginal('billing_address')
            : $actionable->getAttribute('billing_address');

        if (! is_array($value)) {
            return is_scalar($value)
                ? $this->billingAddressNormalizer->normalize($value)
                : null;
        }

        return $this->billingAddressNormalizer->normalize(array_map(
            static fn ($part): bool|float|int|string|null => is_scalar($part) ? $part : null,
            array_replace(
                array_fill_keys(self::BILLING_ADDRESS_KEYS, null),
                array_intersect_key($value, array_flip(self::BILLING_ADDRESS_KEYS)),
            ),
        ));
    }
}
