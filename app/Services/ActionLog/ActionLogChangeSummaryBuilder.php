<?php

declare(strict_types=1);

namespace App\Services\ActionLog;

use App\DTOs\ActionLog\ActionLogSummary;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

readonly class ActionLogChangeSummaryBuilder
{
    public function __construct(
        private ActionLogFieldCatalog $fieldCatalog,
        private ActionLogValueFormatter $valueFormatter,
    ) {
    }

    public function build(Model $actionable): ?ActionLogSummary
    {
        $trackedFields = $this->fieldCatalog->trackedFields($actionable);
        $dirtyFields = array_keys($actionable->getDirty());
        $originalAttributes = $this->snapshot($actionable, $trackedFields, true);
        $currentAttributes = $this->snapshot($actionable, $trackedFields, false);
        $lines = $this->buildLines(
            trackedFields: $trackedFields,
            dirtyFields: $dirtyFields,
            originalAttributes: $originalAttributes,
            currentAttributes: $currentAttributes,
        );

        if ($lines === []) {
            return null;
        }

        return new ActionLogSummary(
            title: $this->fieldCatalog->title($actionable),
            body: implode("\n", $lines),
        );
    }

    /**
     * @param  list<string>  $fields
     *
     * @return array<string, BackedEnum|DateTimeInterface|scalar|null>
     */
    private function snapshot(Model $actionable, array $fields, bool $original): array
    {
        $snapshot = [];

        foreach ($fields as $field) {
            $snapshot[$field] = $this->normalizedValue($actionable, $field, $original);
        }

        return $snapshot;
    }

    private function normalizedValue(
        Model $actionable,
        string $field,
        bool $original,
    ): BackedEnum|DateTimeInterface|float|int|string|bool|null {
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
     * @param  list<string>  $trackedFields
     * @param  list<string>  $dirtyFields
     * @param  array<string, BackedEnum|DateTimeInterface|scalar|null>  $originalAttributes
     * @param  array<string, BackedEnum|DateTimeInterface|scalar|null>  $currentAttributes
     *
     * @return list<string>
     */
    private function buildLines(
        array $trackedFields,
        array $dirtyFields,
        array $originalAttributes,
        array $currentAttributes,
    ): array {
        $lines = [];

        foreach ($trackedFields as $field) {
            $line = $this->resolveLine($field, $dirtyFields, $originalAttributes, $currentAttributes);

            if ($line !== null) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * @param  list<string>  $dirtyFields
     * @param  array<string, BackedEnum|DateTimeInterface|scalar|null>  $originalAttributes
     * @param  array<string, BackedEnum|DateTimeInterface|scalar|null>  $currentAttributes
     */
    private function resolveLine(
        string $field,
        array $dirtyFields,
        array $originalAttributes,
        array $currentAttributes,
    ): ?string {
        if (! in_array($field, $dirtyFields, true)) {
            return null;
        }

        $oldValue = $this->valueFormatter->format($field, $originalAttributes[$field] ?? null, $originalAttributes);
        $newValue = $this->valueFormatter->format($field, $currentAttributes[$field] ?? null, $currentAttributes);

        return $oldValue === $newValue
            ? null
            : sprintf('%s: %s -> %s', $this->fieldCatalog->fieldLabel($field), $oldValue, $newValue);
    }
}
