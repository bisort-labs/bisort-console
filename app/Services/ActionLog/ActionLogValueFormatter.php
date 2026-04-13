<?php

declare(strict_types=1);

namespace App\Services\ActionLog;

use App\Enums\DealStage;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\ClientProject;
use App\Models\Lead;
use App\Models\User;
use BackedEnum;
use DateTimeInterface;

readonly class ActionLogValueFormatter
{
    private const array ENUM_FIELDS = [
        'source' => LeadSource::class,
        'stage' => DealStage::class,
        'status' => LeadStatus::class,
    ];

    private const array RELATION_FIELDS = [
        'lead_id' => Lead::class,
        'owner_id' => User::class,
        'project_id' => ClientProject::class,
    ];

    public function __construct(
        private ActionLogEnumFormatter $enumFormatter,
        private ActionLogPrimitiveValueFormatter $primitiveFormatter,
        private ActionLogRelationValueFormatter $relationFormatter,
    ) {
    }

    /**
     * @param  array<string, BackedEnum|DateTimeInterface|scalar|null>  $snapshot
     */
    public function format(
        string $field,
        BackedEnum|DateTimeInterface|float|int|string|bool|null $value,
        array $snapshot,
    ): string {
        if (array_key_exists($field, self::ENUM_FIELDS)) {
            return $this->enumFormatter->format($value, self::ENUM_FIELDS[$field]);
        }

        if (array_key_exists($field, self::RELATION_FIELDS)) {
            return $this->relationFormatter->format($value, self::RELATION_FIELDS[$field]);
        }

        return $this->primitiveFormatter->format($field, $value, $snapshot);
    }
}
