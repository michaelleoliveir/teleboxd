<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['tmdb_id', 'name'])]
class Genre extends Model
{
    public function shows(): BelongsToMany
    {
        return $this->belongsToMany(Show::class);
    }
}
