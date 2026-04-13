<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActionLogType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property ActionLogType $type
 * @property string|null $title
 * @property string|null $body
 * @property User|null $actor
 * @property Carbon $happened_at
 */
#[Fillable([
    'type',
    'title',
    'body',
    'actor_id',
    'happened_at',
])]
class ActionLog extends Model
{
    use HasTimestamps, SoftDeletes;

    /**
     * @return MorphTo<Model, $this>
     */
    public function actionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => ActionLogType::class,
            'happened_at' => 'datetime',
        ];
    }
}
