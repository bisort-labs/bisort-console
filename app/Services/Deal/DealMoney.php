<?php

declare(strict_types=1);

namespace App\Services\Deal;

use InvalidArgumentException;
use Safe\Exceptions\PcreException;
use function Safe\preg_match;

class DealMoney
{
    public static function amountToCents(int|float|string|null $amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        if (is_int($amount)) {
            return $amount * 100;
        }

        [$wholeUnits, $fractionalUnits] = self::splitAmount(
            self::normalizeAmount($amount),
        );

        return ((int) $wholeUnits * 100) + (int) str_pad($fractionalUnits, 2, '0');
    }

    public static function centsToAmount(int|string|null $cents): string
    {
        $normalizedCents = (int) ($cents ?? 0);

        return sprintf(
            '%d.%02d',
            intdiv($normalizedCents, 100),
            $normalizedCents % 100,
        );
    }

    /**
     * @throws PcreException
     */
    private static function normalizeAmount(float|string $amount): string
    {
        if (is_float($amount)) {
            $amount = number_format($amount, 2, '.', '');
        }

        $normalizedAmount = str_replace(',', '.', trim($amount));

        if (preg_match('/^\d+(?:\.\d{1,2})?$/', $normalizedAmount) !== 1) {
            throw new InvalidArgumentException('Expected a positive decimal amount with up to two decimal places.');
        }

        return $normalizedAmount;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function splitAmount(string $normalizedAmount): array
    {
        $amountParts = explode('.', $normalizedAmount, 2);

        return [$amountParts[0], $amountParts[1] ?? ''];
    }
}
