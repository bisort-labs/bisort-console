<?php

declare(strict_types=1);

namespace App\Support\ActionLogs;

use App\Support\Deals\DealMoney;
use App\Support\Localization;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ActionLogPrimitiveValueFormatter
{
    /**
     * @param  array<string, BackedEnum|DateTimeInterface|float|int|string|null>  $snapshot
     */
    public function format(
        string $field,
        BackedEnum|DateTimeInterface|float|int|string|null $value,
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

    private function formatCurrency(BackedEnum|DateTimeInterface|float|int|string|null $value): string
    {
        return is_scalar($value) && filled($value)
            ? strtoupper(strval($value))
            : Localization::translate('common.placeholder');
    }

    private function formatDate(BackedEnum|DateTimeInterface|float|int|string|null $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        return is_scalar($value) && filled($value)
            ? Carbon::parse(strval($value))->toDateString()
            : Localization::translate('common.placeholder');
    }

    /**
     * @param  array<string, BackedEnum|DateTimeInterface|float|int|string|null>  $snapshot
     */
    private function formatMoney(
        BackedEnum|DateTimeInterface|float|int|string|null $value,
        array $snapshot,
    ): string {
        if (! is_numeric($value)) {
            return Localization::translate('common.placeholder');
        }

        $currency = $this->formatCurrency($snapshot['currency'] ?? null);

        return "{$currency} " . DealMoney::centsToAmount((int) $value);
    }

    private function formatProbability(BackedEnum|DateTimeInterface|float|int|string|null $value): string
    {
        return is_numeric($value)
            ? sprintf('%d%%', (int) $value)
            : Localization::translate('common.placeholder');
    }

    private function formatText(BackedEnum|DateTimeInterface|float|int|string|null $value): string
    {
        if (! is_scalar($value) || blank($value)) {
            return Localization::translate('common.placeholder');
        }

        return Str::of(strval($value))->squish()->value();
    }
}
