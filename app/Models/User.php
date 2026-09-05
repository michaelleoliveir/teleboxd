<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $bio
 * @property string|null $avatar_path
 * @property string $slug
 */
#[Fillable(['name', 'email', 'password', 'bio', 'avatar_path', 'slug'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->isDirty('name') || blank($user->slug)) {
                $user->slug = $user->uniqueSlugFromName();
            }
        });
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1) . Str::substr($initials, -1)
            : $initials;
    }

    /** @return BelongsToMany<Show, $this> */
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Show::class, 'favorites')->withTimestamps();
    }

    /** @return BelongsToMany<Show, $this> */
    public function wishlists(): BelongsToMany
    {
        return $this->belongsToMany(Show::class, 'wishlists')->withTimestamps();
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** @return BelongsToMany<Season, $this> */
    public function watchedSeasons(): BelongsToMany
    {
        return $this->belongsToMany(Season::class, 'watched_seasons')->withTimestamps()->withPivot('last_watched_episode');
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get a unique slug from the user's name.
     *
     * @return string
     */
    public function uniqueSlugFromName(): string
    {
        $base = Str::slug($this->name) ?: 'user';
        $slug = $base;
        $i = 1;
        while (static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn($q) => $q->where('id', '!=', $this->id))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}
