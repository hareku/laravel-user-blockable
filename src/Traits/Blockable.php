<?php

namespace Hareku\LaravelBlockable\Traits;

use Hareku\LaravelBlockable\Models\BlockRelationship;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

trait Blockable
{
    /**
     * @return HasMany
     */
    public function blockerRelationships(): HasMany
    {
        return $this->hasMany(BlockRelationship::class, 'blocked_by_id');
    }

    /**
     * @return HasMany
     */
    public function blockingRelationships(): HasMany
    {
        return $this->hasMany(BlockRelationship::class, 'blocker_id');
    }

    /**
     * @return BelongsToMany
     */
    public function blockerUsers(): BelongsToMany
    {
        return $this->belongsToMany(
                config('blockable.user'),
                config('blockable.table_name'),
                'blocked_by_id',
                'blocker_id'
            )
            ->withPivot('blocked_at');
    }

    /**
     * @return BelongsToMany
     */
    public function blockingUsers(): BelongsToMany
    {
        return $this->belongsToMany(
                config('blockable.user'),
                config('blockable.table_name'),
                'blocker_id',
                'blocked_by_id'
            )
            ->withPivot('blocked_at');
    }

    /**
     * Block.
     *
     * @param  array|int  $ids
     * @return array
     */
    public function block($ids): array
    {
        $ids = $this->mergeBlcokedAt((array) $ids);

        return $this->blockingUsers()->syncWithoutDetaching($ids);
    }

    /**
     * Add blockers.
     *
     * @param  array|int  $ids
     * @return array
     */
    public function addBlockers($ids): array
    {
        $ids = $this->mergeBlcokedAt((array) $ids);

        return $this->blockerUsers()->syncWithoutDetaching($ids);
    }

    /**
     * Unblock.
     *
     * @param  mixed  $ids
     * @return int
     */
    public function unblock($ids): int
    {
        return $this->blockingUsers()->detach($ids);
    }

    /**
     * Merge blocked_at to array for relationships table.
     *
     * @param  array  $ids
     * @return array
     */
    private function mergeBlcokedAt(array $ids): array
    {
        $mergedIds = [];
        $blockedAt = new Carbon;

        foreach ($ids as $id) {
            $mergedIds[$id] = ['blocked_at' => $blockedAt];
        }

        return $mergedIds;
    }

    /**
     * Check if it is blocking.
     *
     * @param  array|int  $id
     * @return bool
     */
    public function isBlocking($id): bool
    {
        if (is_array($id)) {
            return count($id) === $this->blockingUsers()->whereIn('blocked_by_id', $id)->count();
        }

        return $this->blockingUsers()->where('blocked_by_id', $id)->exists();
    }

    /**
     * Check if it is being blocked.
     *
     * @param  array|int  $id
     * @return bool
     */
    public function isBlockedBy($id): bool
    {
        if (is_array($id)) {
            return count($id) === $this->blockerUsers()->whereIn('blocker_id', $id)->count();
        }

        return $this->blockerUsers()->where('blocker_id', $id)->exists();
    }

    /**
     * Check if it is mutual block.
     *
     * @param  array|int  $id
     * @return bool
     */
    public function isMutualBlock($id): bool
    {
        return $this->isBlocking($id) && $this->isBlockedBy($id);
    }

    /**
     * Get blocker user IDs.
     *
     * @param  bool  $collection
     * @return array|\Illuminate\Support\Collection
     */
    public function blockerIds(bool $collection = false)
    {
        $ids = $this->blockerUsers()->pluck($this->getTable().'.id');

        if ($collection) {
            return $ids;
        }

        return $ids->toArray();
    }

    /**
     * Get blocking user IDs.
     *
     * @param  bool  $collection
     * @return array|\Illuminate\Support\Collection
     */
    public function blockingIds(bool $collection = false)
    {
        $ids = $this->blockingUsers()->pluck($this->getTable().'.id');

        if ($collection) {
            return $ids;
        }

        return $ids->toArray();
    }

    /**
     * Reject IDs that is not a blocker user from the given array.
     *
     * @param  array  $ids
     * @return array
     */
    public function rejectNotBlocker(array $ids): array
    {
        return BlockRelationship::where('blocked_by_id', $this->id)
                                ->whereIn('blocker_id', $ids)
                                ->pluck('blocker_id')
                                ->toArray();
    }

    /**
     * Reject IDs that is not blocking user from the given array.
     *
     * @param  array  $ids
     * @return array
     */
    public function rejectNotBlocking(array $ids): array
    {
        return BlockRelationship::where('blocker_id', $this->id)
                                ->whereIn('blocked_by_id', $ids)
                                ->pluck('blocked_by_id')
                                ->toArray();
    }
}
