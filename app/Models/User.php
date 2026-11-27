<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
 * @property-read string|null $avatar_url
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

        static::deleted(function (User $user): void {
            if ($user->avatar_path !== null) {
                Storage::disk('public')->delete($user->avatar_path);
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
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    /**
     * Get the user's profile picture
     *
     * @return Attribute<string|null, never>
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar_path ? Storage::url($this->avatar_path) : null
        );
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

    /** @return BelongsToMany<User, $this> */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')->withTimestamps();
    }

    /** @return BelongsToMany<User, $this> */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')->withTimestamps();
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeWithFollowedBy(Builder $query, ?int $userId): Builder
    {
        return $query->withExists(['followers as followed_by_me' => fn ($q) => $q->where('follower_id', $userId)]);
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeWithListStats(Builder $query): Builder
    {
        return $query->withCount(['followers', 'following', 'reviews', 'favorites', 'watchedSeasons as watched_count' => fn ($q) => $q->select(DB::raw('count(distinct seasons.show_id)'))]);
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeMembersFor(Builder $query, int $userId): Builder
    {
        return $query->whereKeyNot($userId)->withFollowedBy($userId);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get a unique slug from the user's name.
     */
    public function uniqueSlugFromName(): string
    {
        $base = Str::slug($this->name) ?: 'user';
        $slug = $base;
        $i = 1;
        while (static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn ($q) => $q->where('id', '!=', $this->id))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
