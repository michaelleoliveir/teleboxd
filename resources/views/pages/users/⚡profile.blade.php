<?php

use App\Models\User;
use App\Services\WatchStatsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile |')] class extends Component
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
            ->take(6);
    }

    #[Computed]
    public function stats(): array
    {
        $service = app(WatchStatsService::class);

        return [
            'episodes_watched' => $service->watchedEpisodesCount($this->user),
            'hours_watched' => $service->hoursWatched($this->user),
            'shows_completed' => $service->showsCompletedCount($this->user),
        ];
    }
};
?>

<div>
    <div class="mb-9 flex flex-wrap items-end justify-between gap-6">
        <div class="flex min-w-0 items-center gap-4">
            @if ($user->avatar_path)
                <img src="{{ Storage::url($user->avatar_path) }}" alt="{{ $user->name }}"
                    class="size-16 shrink-0 rounded-full object-cover sm:size-20">
            @else
                <flux:avatar :name="$user->name" :initials="$user->initials()" class="size-16 shrink-0 sm:size-20" />
            @endif

            <div class="min-w-0">
                <div class="flex flex-wrap items-baseline gap-x-2.5 gap-y-1">
                    <h1 class="font-serif text-3xl text-zinc-900 italic sm:text-4xl dark:text-white">{{ $user->name }}</h1>

                    @if ($this->isOwner)
                        <span class="text-tlbx-muted" aria-hidden="true">·</span>
                        <a href="{{ route('profile.edit') }}" wire:navigate
                            class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase underline-offset-4 hover:text-tlbx-primary hover:underline">
                            {{ __('Edit profile') }}
                        </a>
                    @endif
                </div>

                @if ($user->bio)
                    <p class="mt-1.5 flex max-w-lg items-center gap-2.5">
                        <span class="w-px shrink-0 self-stretch bg-tlbx-border"></span>
                        <span class="font-serif text-sm leading-snug text-tlbx-muted italic whitespace-pre-line">{{ trim($user->bio) }}</span>
                    </p>
                @endif
            </div>
        </div>

        <div class="flex gap-8 text-right">
            <div>
                <div class="font-serif text-3xl text-zinc-900 dark:text-white">{{ $this->stats['episodes_watched'] }}</div>
                <div class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">{{ __('Episodes') }}</div>
            </div>
            <div>
                <div class="font-serif text-3xl text-zinc-900 dark:text-white">{{ number_format($this->stats['hours_watched'], 0) }}h</div>
                <div class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">{{ __('Hours') }}</div>
            </div>
            <div>
                <div class="font-serif text-3xl text-zinc-900 dark:text-white">{{ $this->stats['shows_completed'] }}</div>
                <div class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">{{ __('Completed') }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-12 lg:grid-cols-3">
        <div class="flex flex-col gap-10 lg:col-span-2">
            <section>
                <div class="mb-4 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Favorite shows') }}</div>

                @if ($this->favorites->isEmpty())
                    <p class="font-serif text-sm text-tlbx-muted italic">{{ __('No favorite shows yet.') }}</p>
                @else
                    <div class="tlbx-scrollrow flex gap-3 overflow-x-auto px-1 pb-3">
                        @foreach ($this->favorites as $show)
                            <a href="{{ route('shows.show', $show) }}" wire:navigate class="w-24 shrink-0 sm:w-28">
                                <x-poster-card
                                    :poster="$show->poster_path ? 'https://image.tmdb.org/t/p/w500'.$show->poster_path : null"
                                    :title="$show->name" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
                <div class="mb-4 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Recent activity') }}</div>

                @if ($this->activity->isEmpty())
                    <p class="font-serif text-sm text-tlbx-muted italic">{{ __('No recent activity.') }}</p>
                @else
                    <div class="tlbx-scrollrow flex gap-3 overflow-x-auto px-1 pb-3">
                        @foreach ($this->activity as $item)
                            <a href="{{ route('shows.show', $item['show']) }}" wire:navigate class="w-24 shrink-0 sm:w-28">
                                <x-poster-card
                                    :poster="$item['show']->poster_path ? 'https://image.tmdb.org/t/p/w500'.$item['show']->poster_path : null"
                                    :title="$item['show']->name"
                                    :rating="$item['type'] === 'review' ? $item['rating'] : null" />

                                @if ($item['type'] === 'watched' && $item['season'])
                                    <p class="mt-1 text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">
                                        {{ __('Season :number', ['number' => $item['season']->season_number]) }}
                                    </p>
                                @elseif ($item['type'] === 'watchlist')
                                    <p class="mt-1 text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">{{ __('Watchlist') }}</p>
                                @elseif ($item['type'] === 'favorite')
                                    <p class="mt-1 text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">{{ __('Favorited') }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <section>
                <div class="mb-4 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Recent reviews') }}</div>

                @if ($this->reviews->isEmpty())
                    <p class="font-serif text-sm text-tlbx-muted italic">{{ __('No reviews yet.') }}</p>
                @else
                    <div class="divide-y divide-tlbx-border">
                        @foreach ($this->reviews as $review)
                            @continue(! $review->show)
                            <div class="flex gap-3 py-5 first:pt-0">
                                <a href="{{ route('shows.show', $review->show) }}" wire:navigate class="w-16 shrink-0">
                                    <div class="aspect-2/3 overflow-hidden rounded-md border border-tlbx-border bg-tlbx-card">
                                        @if ($review->show->poster_path)
                                            <img src="https://image.tmdb.org/t/p/w185{{ $review->show->poster_path }}"
                                                alt="{{ $review->show->name }}" class="h-full w-full object-cover" />
                                        @endif
                                    </div>
                                </a>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-baseline gap-2">
                                        <a href="{{ route('shows.show', $review->show) }}" wire:navigate
                                            class="text-sm font-semibold text-zinc-900 dark:text-white">
                                            {{ $review->show->name }}
                                        </a>
                                        <x-star-rating :rating="$review->rating" />
                                        <span class="ml-auto text-xs text-tlbx-muted">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if ($review->content)
                                        <p class="mt-2 font-serif text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                                            {{ $review->content }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="flex flex-col gap-10">
            <section>
                <div class="mb-4 flex items-baseline justify-between gap-3">
                    <div class="text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Watchlist') }}</div>
                    <div class="font-serif text-sm text-tlbx-muted">{{ $this->watchlistCount }}</div>
                </div>

                <div class="grid grid-cols-5 gap-1.5">
                    @foreach ($this->watchlist as $show)
                        <a href="{{ route('shows.show', $show) }}" wire:navigate class="min-w-0">
                            <div class="aspect-2/3 overflow-hidden rounded-sm border border-tlbx-border bg-tlbx-card">
                                @if ($show->poster_path)
                                    <img src="https://image.tmdb.org/t/p/w185{{ $show->poster_path }}" alt="{{ $show->name }}"
                                        class="h-full w-full object-cover" />
                                @endif
                            </div>
                        </a>
                    @endforeach

                    @for ($i = $this->watchlist->count(); $i < 5; $i++)
                        <div class="aspect-2/3 rounded-sm border border-tlbx-border bg-tlbx-card/50"></div>
                    @endfor
                </div>
            </section>

            <section>
                <div class="mb-4 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Diary') }}</div>

                @if ($this->diary->isEmpty())
                    <p class="font-serif text-sm text-tlbx-muted italic">{{ __('No watched seasons yet.') }}</p>
                @else
                    <div class="flex flex-col gap-4">
                        @foreach ($this->diary->groupBy(fn ($season) => \Illuminate\Support\Carbon::parse($season->pivot->updated_at)->toDateString()) as $date => $seasons)
                            <div class="flex gap-3">
                                <div class="w-12 shrink-0 text-[10px] leading-tight tracking-[0.15em] text-tlbx-muted uppercase">
                                    {{ \Illuminate\Support\Carbon::parse($date)->format('d M') }}
                                </div>
                                <div class="flex min-w-0 flex-col gap-1">
                                    @foreach ($seasons as $season)
                                        @continue(! $season->show)
                                        <a href="{{ route('shows.show', $season->show) }}" wire:navigate
                                            class="truncate text-sm text-zinc-900 dark:text-white">
                                            {{ $season->show->name }}
                                            <span class="text-tlbx-muted">
                                                · {{ __('S:number', ['number' => $season->season_number]) }}
                                                @if ($season->pivot->last_watched_episode)
                                                    · {{ __('E:number', ['number' => $season->pivot->last_watched_episode]) }}
                                                @endif
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>
    </div>
</div>