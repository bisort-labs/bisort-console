<?php

declare(strict_types=1);

namespace App\Services\ActionLog;

use App\Services\Localization;

readonly class ActionLogBooleanFormatter
{
    public function format(float|int|string|bool|null $value): string
    {
        if (is_bool($value)) {
            return $this->formattedAnswer($value);
        }

        if (is_numeric($value)) {
            return $this->formattedAnswer((int) $value === 1);
        }

        return is_string($value)
            ? $this->formatStringValue($value)
            : Localization::translate('common.placeholder');
    }

    private function formatStringValue(string $value): string
    {
        return match (strtolower(trim($value))) {
            '1', 'true', 'yes' => $this->formattedAnswer(true),
            '0', 'false', 'no' => $this->formattedAnswer(false),
            default => Localization::translate('common.placeholder'),
        };
    }

    private function formattedAnswer(bool $value): string
    {
        return $value
            ? Localization::translate('common.yes')
            : Localization::translate('common.no');
    }
}
