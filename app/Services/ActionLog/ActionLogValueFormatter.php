<?php

declare(strict_types=1);

namespace App\Services\ActionLog;

use App\Enums\CustomerType;
use App\Enums\DealStage;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\ClientProject;
use App\Models\Lead;
use App\Models\User;
use App\Services\BillingAddressFormatter;
use BackedEnum;
use DateTimeInterface;

/**
 * @phpstan-type BillingAddress array{
 *     street: string|null,
 *     city: string|null,
 *     state: string|null,
 *     zip: string|null,
 *     country: string|null
 * }
 */
readonly class ActionLogValueFormatter
{
    private const array ENUM_FIELDS = [
        'source' => LeadSource::class,
        'stage' => DealStage::class,
        'status' => LeadStatus::class,
        'type' => CustomerType::class,
    ];

    private const array RELATION_FIELDS = [
        'lead_id' => Lead::class,
        'owner_id' => User::class,
        'project_id' => ClientProject::class,
    ];

    public function __construct(
        private BillingAddressFormatter $billingAddressFormatter,
        private ActionLogBooleanFormatter $booleanFormatter,
        private ActionLogEnumFormatter $enumFormatter,
        private ActionLogPrimitiveValueFormatter $primitiveFormatter,
        private ActionLogRelationValueFormatter $relationFormatter,
    ) {
    }

    /**
     * @param  array<string, BillingAddress|BackedEnum|DateTimeInterface|scalar|null>  $snapshot
     * @param  BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null  $value
     */
    public function format(
        string $field,
        array|BackedEnum|DateTimeInterface|float|int|string|bool|null $value,
        array $snapshot,
    ): string {
        if ($field === 'billing_address') {
            return $this->billingAddressFormatter->format(is_array($value) ? $value : null);
        }

        if ($field === 'is_vat_exempt') {
            return $this->booleanFormatter->format($this->normalizeScalarValue($value));
        }

        return $this->formattedEnumValue($field, $value)
            ?? $this->formattedRelationValue($field, $value)
            ?? $this->primitiveFormatter->format($field, $value, $snapshot);
    }

    /**
     * @param  BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null  $value
     */
    private function normalizeScalarValue(
        array|BackedEnum|DateTimeInterface|float|int|string|bool|null $value,
    ): float|int|string|bool|null {
        return is_scalar($value) ? $value : null;
    }

    /**
     * @param  BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null  $value
     */
    private function normalizeNonIterableValue(
        array|BackedEnum|DateTimeInterface|float|int|string|bool|null $value,
    ): BackedEnum|DateTimeInterface|float|int|string|bool|null {
        return is_array($value) ? null : $value;
    }

    /**
     * @param  BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null  $value
     */
    private function formattedEnumValue(
        string $field,
        array|BackedEnum|DateTimeInterface|float|int|string|bool|null $value,
    ): ?string {
        if (! array_key_exists($field, self::ENUM_FIELDS)) {
            return null;
        }

        return $this->enumFormatter->format(
            $this->normalizeNonIterableValue($value),
            self::ENUM_FIELDS[$field],
        );
    }

    /**
     * @param  BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null  $value
     */
    private function formattedRelationValue(
        string $field,
        array|BackedEnum|DateTimeInterface|float|int|string|bool|null $value,
    ): ?string {
        if (! array_key_exists($field, self::RELATION_FIELDS)) {
            return null;
        }

        return $this->relationFormatter->format(
            $this->normalizeNonIterableValue($value),
            self::RELATION_FIELDS[$field],
        );
    }
}
