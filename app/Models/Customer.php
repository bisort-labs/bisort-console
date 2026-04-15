<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerType;
use App\Models\Concerns\HasActionLogs;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property array<string, string|null>|null $billing_address
 * @property string|null $country_code
 * @property Carbon|null $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $email
 * @property bool $is_vat_exempt
 * @property string $name
 * @property string|null $phone
 * @property string|null $tax_number
 * @property CustomerType $type
 * @property Carbon|null $updated_at
 * @property string|null $vat_exemption_reason
 * @property string|null $vat_id
 * @property EloquentCollection<int, ActionLog> $actionLogs
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
])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasActionLogs, HasFactory, HasTimestamps, SoftDeletes;

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
