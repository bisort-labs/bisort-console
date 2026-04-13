<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Concerns\HasActionLogs;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $company
 * @property Carbon|null $created_at
 * @property string|null $city
 * @property string|null $country
 * @property Carbon|null $deleted_at
 * @property string|null $phone
 * @property LeadSource|null $source
 * @property string|null $state
 * @property LeadStatus|null $status
 * @property string|null $street
 * @property Carbon|null $updated_at
 * @property string|null $zip
 * @property EloquentCollection<int, Deal> $deals
 * @property EloquentCollection<int, ActionLog> $actionLogs
 * @property User|null $owner
 * @property int|null $owner_id
 */
#[Fillable([
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
])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasActionLogs, HasFactory, HasTimestamps, SoftDeletes;

    /**
     * @return HasMany<Deal, $this>
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'source' => LeadSource::class,
        ];
    }
}
