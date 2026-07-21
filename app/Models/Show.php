<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['tmdb_id', 'name', 'overview', 'poster_path', 'average_rating', 'reviews_count', 'first_air_date', 'synced_at', 'popularity'])]
class Show extends Model
{
    protected $casts = [
        'first_air_date' => 'date',
        'synced_at' => 'datetime',
        'average_rating' => 'decimal:1',
        'popularity' => 'decimal:4'
    ];

    /** @return BelongsToMany<Genre, $this> */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'show_genre');
    }

    /** @return BelongsToMany<Actor, $this> */
    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'show_actor')->withPivot('character', 'popularity_order')->orderByPivot('popularity_order');
    }
}
