<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['tmdb_id', 'name', 'overview', 'poster_path', 'average_rating', 'reviews_count', 'first_air_date', 'synced_at', 'popularity', 'number_of_seasons', 'number_of_episodes', 'episode_run_time'])]
class Show extends Model
{
    protected $casts = [
        'first_air_date' => 'date',
        'synced_at' => 'datetime',
        'average_rating' => 'decimal:1',
        'popularity' => 'decimal:4',
        'number_of_seasons' => 'integer',
        'number_of_episodes' => 'integer',
        'episode_run_time' => 'integer'
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

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /** @return BelongsToMany<User, $this> */
    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    /** @return HasMany<Season, $this> */
    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class)->orderBy('season_number');
    }
}
