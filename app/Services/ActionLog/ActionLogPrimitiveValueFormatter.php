<?php

declare(strict_types=1);

namespace App\Services\ActionLog;

use App\Services\Deal\DealMoney;
use App\Services\Localization;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Support\Carbon;
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
readonly class ActionLogPrimitiveValueFormatter
{
    /**
     * @param  array<string, BillingAddress|BackedEnum|DateTimeInterface|scalar|null>  $snapshot
     * @param  BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null  $value
     */
    public function format(
        string $field,
        array|BackedEnum|DateTimeInterface|float|int|string|bool|null $value,
        array $snapshot,
    ): string {
        return match ($field) {
            'close_date' => $this->formatDate($value),
            'currency' => $this->formatCurrency($value),
            'expected_value_cents' => $this->formatMoney($value, $snapshot),
            'probability' => $this->formatProbability($value),
            default => $this->formatText($value),
        };
    }

    /**
     * @param  BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null  $value
     */
    private function formatCurrency(array|BackedEnum|DateTimeInterface|float|int|string|bool|null $value): string
    {
        return is_scalar($value) && filled($value)
            ? strtoupper(strval($value))
            : Localization::translate('common.placeholder');
    }

    /**
     * @param  BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null  $value
     */
    private function formatDate(array|BackedEnum|DateTimeInterface|float|int|string|bool|null $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        return is_scalar($value) && filled($value)
            ? Carbon::parse(strval($value))->toDateString()
            : Localization::translate('common.placeholder');
    }

    /**
     * @param  array<string, BillingAddress|BackedEnum|DateTimeInterface|scalar|null>  $snapshot
     * @param  BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null  $value
     */
    private function formatMoney(
        array|BackedEnum|DateTimeInterface|float|int|string|bool|null $value,
        array $snapshot,
    ): string {
        if (! is_numeric($value)) {
            return Localization::translate('common.placeholder');
        }

        $currency = $this->formatCurrency($snapshot['currency'] ?? null);

        return "{$currency} " . DealMoney::centsToAmount((int) $value);
    }

    /**
     * @param  BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null  $value
     */
    private function formatProbability(array|BackedEnum|DateTimeInterface|float|int|string|bool|null $value): string
    {
        return is_numeric($value)
            ? sprintf('%d%%', (int) $value)
            : Localization::translate('common.placeholder');
    }

    /**
     * @param  BillingAddress|BackedEnum|DateTimeInterface|float|int|string|bool|null  $value
     */
    private function formatText(array|BackedEnum|DateTimeInterface|float|int|string|bool|null $value): string
    {
        if (! is_scalar($value) || blank($value)) {
            return Localization::translate('common.placeholder');
        }

        return Str::of(strval($value))->squish()->value();
    }
}
