<?php

declare(strict_types=1);

namespace App\Services\ActionLog;

use App\Services\Localization;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

readonly class ActionLogRelationValueFormatter
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function format(
        BackedEnum|DateTimeInterface|float|int|string|bool|null $value,
        string $modelClass,
    ): string {
        if (! is_numeric($value)) {
            return Localization::translate('common.placeholder');
        }

        /** @var string|null $name */
        $name = $modelClass::query()
            ->whereKey((int) $value)
            ->value('name');

        return filled($name) ? $name : Localization::translate('common.placeholder');
    }
}
