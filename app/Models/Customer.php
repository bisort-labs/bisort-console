<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerType;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property array<string, string|null>|null $billing_address
 * @property EloquentCollection<int, ActionLog> $actionLogs
 * @property bool $is_vat_exempt
 * @property CustomerType $type
 */
#[Fillable([
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
    'payment_terms_days',
])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, HasTimestamps, SoftDeletes;

    /**
     * @return MorphMany<ActionLog, $this>
     */
    public function actionLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'actionable');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => CustomerType::class,
            'billing_address' => 'array',
            'is_vat_exempt' => 'bool',
        ];
    }
}
