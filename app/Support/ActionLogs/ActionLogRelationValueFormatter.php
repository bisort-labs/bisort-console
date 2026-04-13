<?php

declare(strict_types=1);

namespace App\Support\ActionLogs;

use App\Support\Localization;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class ActionLogRelationValueFormatter
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function format(
        BackedEnum|DateTimeInterface|float|int|string|null $value,
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
