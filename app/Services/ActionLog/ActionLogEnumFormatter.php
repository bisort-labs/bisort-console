<?php

declare(strict_types=1);

namespace App\Services\ActionLog;

use App\Services\Localization;
use BackedEnum;
use DateTimeInterface;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

readonly class ActionLogEnumFormatter
{
    /**
     * @param  class-string<BackedEnum&HasLabel>  $enumClass
     */
    public function format(
        BackedEnum|DateTimeInterface|float|int|string|bool|null $value,
        string $enumClass,
    ): string {
        $enum = $this->resolveEnum($value, $enumClass);

        return $enum instanceof HasLabel
            ? $this->stringifyLabel($enum->getLabel())
            : Localization::translate('common.placeholder');
    }

    /**
     * @param  class-string<BackedEnum&HasLabel>  $enumClass
     */
    private function resolveEnum(
        BackedEnum|DateTimeInterface|float|int|string|bool|null $value,
        string $enumClass,
    ): ?HasLabel {
        if ($value instanceof BackedEnum && $value instanceof HasLabel) {
            return $value;
        }

        if (! is_scalar($value) || ! filled($value)) {
            return null;
        }

        return $enumClass::tryFrom(strval($value));
    }

    private function stringifyLabel(Htmlable|string|null $label): string
    {
        if ($label instanceof Htmlable) {
            return $label->toHtml();
        }

        return $label ?? Localization::translate('common.placeholder');
    }
}
