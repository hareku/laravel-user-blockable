<?php

namespace Hareku\LaravelBlockable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockRelationship extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['blocker_id', 'blocked_by_id', 'blocked_at'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['blocked_at'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('blockable.table_name');
    }

    /**
     * @return BelongsTo
     */
    public function blocker(): BelongsTo
    {
        return $this->belongsTo(config('blockable.user'), 'blocker_id');
    }

    /**
     * @return BelongsTo
     */
    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(config('blockable.user'), 'blocked_by_id');
    }
}
