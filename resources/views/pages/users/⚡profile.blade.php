<?php

use App\Models\User;
use App\Services\WatchStatsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public User $user;

    #[Computed]
    public function isOwner(): bool
    {
        return Auth::id() === $this->user->id;
    }

    #[Computed]
    public function favorites(): Collection
    {
        return $this->user->favorites()
            ->latest('favorites.created_at')
            ->limit(4)
            ->get();
    }

    #[Computed]
    public function watchlist(): Collection
    {
        return $this->user->wishlists()
            ->latest('wishlists.created_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function watchlistCount(): int
    {
        return $this->user->wishlists()->count();
    }

    #[Computed]
    public function reviews(): Collection
    {
        return $this->user->reviews()
            ->with('show')
            ->latest()
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function diary(): Collection
    {
        return $this->user->watchedSeasons()
            ->with('show')
            ->orderByDesc('watched_seasons.updated_at')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function activity(): Collection
    {
        $reviews = $this->reviews->map(fn($review) => [
            'type' => 'review',
            'show' => $review->show,
            'occurred_at' => $review->created_at,
            'rating' => $review->rating,
            'season' => null
        ]);

        $watched = $this->diary->map(fn($season) => [
            'type' => 'watched',
            'show' => $season->show,
            'occurred_at' => $season->pivot->updated_at,
            'rating' => null,
            'season' => $season
        ]);

        $wishlist = $this->watchlist->map(fn($show) => [
            'type' => 'watchlist',
            'show' => $show,
            'occurred_at' => $show->pivot->created_at,
            'rating' => null,
            'season' => null
        ]);

        $favorites = $this->favorites->map(fn($show) => [
            'type' => 'favorite',
            'show' => $show,
            'occurred_at' => $show->pivot->created_at,
            'rating' => null,
            'season' => null
        ]);

        return collect()
            ->concat($reviews)
            ->concat($watched)
            ->concat($wishlist)
            ->concat($favorites)
            ->filter(fn(array $activity) => $activity['show'] && $activity['occurred_at'])
            ->sortByDesc('occurred_at')
            ->values()
            ->take(8);
    }

    #[Computed]
    public function stats(WatchStatsService $service): array
    {
        return [
            'episodes_watched' => $service->watchedEpisodesCount($this->user),
            'hours_watched' => $service->hoursWatched($this->user),
            'shows_completed' => $service->showsCompletedCount($this->user),
        ];
    }
};
?>

<div>
    {{-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison --}}
</div>