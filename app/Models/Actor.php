<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['tmdb_id', 'name', 'profile_path'])]
class Actor extends Model
{
    /** @return BelongsToMany<Show, $this> */
    public function shows(): BelongsToMany
    {
        return $this->belongsToMany(Show::class, 'show_actor');
    }
}
