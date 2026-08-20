<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['show_id', 'season_number', 'name', 'episode_count', 'air_date'])]
class Season extends Model
{
    protected $casts = [
        'air_date' => 'date',
        'season_number' => 'integer',
        'episode_count' => 'integer'
    ];

    /** @return BelongsTo<Show, $this> */
    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function watchedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'watched_seasons')->withTimestamps();
    }
}
