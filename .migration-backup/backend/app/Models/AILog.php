<?php
declare(strict_types=1);

namespace App\Models;

use Schema;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AILog
 *
 * @property int $id
 * @property int $model_id
 * @property string $mode_type
 * @property int $user_id
 * @property string $locale
 * @property array $content
 * @property int $total_token
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 * @method static Builder|self active()
 * @method static Builder|self filter(array $filter)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereId($value)
 * @mixin Eloquent
 */
class AILog extends Model
{
    public $guarded = ['id'];

    protected $table = 'ai_logs';

    protected $casts = [
        'content' => 'array'
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'user_id', 'user_id');
    }

    public function scopeFilter($query, array $filter): void
    {
        $column = $filter['column'] ?? 'id';
        $sort   = $filter['sort'] ?? 'desc';

        if ($column !== 'id') {
            $column = Schema::hasColumn('ai_logs', $column) ? $column : 'id';
        }

        $query
            ->when(data_get($filter, 'user_id'),          fn($q, $id)   => $q->where('user_id', $id))
            ->when(data_get($filter, 'total_token_from'), fn($q, $from) => $q->where('total_token', '>=', $from))
            ->when(data_get($filter, 'total_token_to'),   fn($q, $to)   => $q->where('total_token', '<=', $to))
            ->when(data_get($filter, 'date_from'), fn($q, $from)   => $q->where('created_at', '>=', $from))
            ->when(data_get($filter, 'date_to'),   fn($q, $to)     => $q->where('created_at', '<=', $to))
            ->when(data_get($filter, 'shop_id'),   fn($q, $id)     => $q->whereHas('shop', fn($q) => $q->where('user_id', $id)))
            ->when(data_get($filter, 'locale'),    fn($q, $locale) => $q->where('locale', $locale))
            ->when(data_get($filter, 'model_id'),  fn($q, $id)     => $q->where('model_id', $id))
            ->when(data_get($filter, 'mode_type'), fn($q, $ype)    => $q->where('mode_type', $ype))
            ->orderBy($column, $sort);
    }
}
