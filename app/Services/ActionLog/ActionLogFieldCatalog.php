<?php

declare(strict_types=1);

namespace App\Services\ActionLog;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\Lead;
use App\Services\Localization;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

readonly class ActionLogFieldCatalog
{
    private const array CUSTOMER_TRACKED_FIELDS = [
        'name',
        'type',
        'email',
        'phone',
        'country_code',
        'vat_id',
        'tax_number',
        'is_vat_exempt',
        'vat_exemption_reason',
        'billing_address',
    ];

    private const array DEAL_TRACKED_FIELDS = [
        'lead_id',
        'project_id',
        'title',
        'stage',
        'expected_value_cents',
        'currency',
        'probability',
        'close_date',
        'lost_reason',
        'notes',
        'owner_id',
    ];

    private const array FIELD_LABELS = [
        'billing_address' => 'fields.billing_address',
        'country_code' => 'fields.country_code',
        'email' => 'fields.email_address',
        'expected_value_cents' => 'fields.expected_value',
        'is_vat_exempt' => 'fields.is_vat_exempt',
        'lead_id' => 'fields.lead',
        'owner_id' => 'fields.owner',
        'project_id' => 'fields.project',
        'tax_number' => 'fields.tax_number',
        'vat_exemption_reason' => 'fields.vat_exemption_reason',
        'vat_id' => 'fields.vat_id',
    ];

    private const array LEAD_TRACKED_FIELDS = [
        'name',
        'email',
        'company',
        'street',
        'city',
        'state',
        'zip',
        'country',
        'phone',
        'source',
        'status',
        'owner_id',
    ];

    public function fieldLabel(string $field): string
    {
        return Localization::translate(self::FIELD_LABELS[$field] ?? sprintf('fields.%s', $field));
    }

    public function title(Model $actionable): string
    {
        return Localization::translate(match (true) {
            $actionable instanceof Customer => 'messages.timeline.customer_details_updated',
            $actionable instanceof Deal => 'messages.timeline.deal_details_updated',
            $actionable instanceof Lead => 'messages.timeline.lead_details_updated',
            default => throw new RuntimeException('Unsupported actionable model for change summaries.'),
        });
    }

    /**
     * @return list<string>
     */
    public function trackedFields(Model $actionable): array
    {
        return match (true) {
            $actionable instanceof Customer => self::CUSTOMER_TRACKED_FIELDS,
            $actionable instanceof Deal => self::DEAL_TRACKED_FIELDS,
            $actionable instanceof Lead => self::LEAD_TRACKED_FIELDS,
            default => throw new RuntimeException('Unsupported actionable model for change summaries.'),
        };
    }
}
