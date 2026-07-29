<x-layouts::app :title="$show->name">
    <div class="flex flex-col gap-9 sm:flex-row">
        <div class="w-44 shrink-0 sm:w-56">
            <div class="aspect-2/3 overflow-hidden rounded-md border border-tlbx-border bg-tlbx-card shadow-sm">
                @if ($show->poster_path)
                    <img
                        src="https://image.tmdb.org/t/p/w500{{ $show->poster_path }}"
                        alt="{{ $show->name }}"
                        class="h-full w-full object-cover"
                    />
                @else
                    <div class="flex h-full w-full items-center justify-center text-xs text-tlbx-muted">
                        {{ __('No poster') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="flex-1 pt-1">
            <div class="text-xs tracking-[0.2em] text-tlbx-muted uppercase">
                {{ $show->first_air_date?->format('Y') ?? __('Unknown year') }}
            </div>

            <h1 class="mt-2 font-serif text-4xl text-zinc-900 italic sm:text-5xl dark:text-white">
                {{ $show->name }}
            </h1>

            @if ($show->genres->isNotEmpty())
                <div class="mt-2 text-sm text-tlbx-muted">
                    {{ $show->genres->pluck('name')->join(' · ') }}
                </div>
            @endif

            <div class="mt-5 flex items-center gap-3">
                <x-star-rating :rating="$show->average_rating" size="size-5" />
                <span class="font-serif text-lg text-zinc-900 dark:text-white">{{ $show->average_rating ?? '—' }}</span>
                <span class="text-xs text-tlbx-muted">
                    {{ trans_choice(':count review|:count reviews', $show->reviews_count, ['count' => $show->reviews_count]) }}
                </span>
            </div>

            @if ($show->overview)
                <p class="mt-6 max-w-2xl text-[15px] leading-relaxed text-zinc-700 dark:text-zinc-300">
                    {{ $show->overview }}
                </p>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                <livewire:pages::shows.favorite-button :show="$show" />

                {{-- quero assistir / review viram componentes Livewire aqui --}}
                <div class="flex items-center gap-2 rounded-sm border border-tlbx-border px-4 py-2.5 text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                    <flux:icon.bookmark class="size-4" />
                    {{ __('Add to watchlist') }}
                </div>
                <div class="flex items-center gap-2 rounded-sm border border-tlbx-border px-4 py-2.5 text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                    <flux:icon.pencil-square class="size-4" />
                    {{ __('Review') }}
                </div>
            </div>
        </div>
    </div>

    <div class="mt-9 mb-12 flex flex-wrap gap-x-14 gap-y-4 border-y border-tlbx-border py-5">
        <div>
            <div class="font-serif text-2xl text-zinc-900 dark:text-white">{{ $show->first_air_date?->format('Y') ?? '—' }}</div>
            <div class="mt-0.5 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Premiere') }}</div>
        </div>
        <div>
            <div class="font-serif text-2xl text-zinc-900 dark:text-white">{{ $show->genres->count() }}</div>
            <div class="mt-0.5 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Genres') }}</div>
        </div>
        <div>
            <div class="font-serif text-2xl text-zinc-900 dark:text-white">{{ $show->actors->count() }}</div>
            <div class="mt-0.5 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Cast members') }}</div>
        </div>
    </div>

    @if ($show->actors->isNotEmpty())
        <section class="mb-12">
            <div class="mb-4 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Cast') }}</div>

            <div class="tlbx-scrollrow flex gap-6 overflow-x-auto px-1 pb-3">
                @foreach ($show->actors as $actor)
                    <x-actor-card
                        :photo="$actor->profile_path ? 'https://image.tmdb.org/t/p/w185'.$actor->profile_path : null"
                        :name="$actor->name"
                        :subtitle="$actor->pivot->character"
                    />
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <div class="mb-4 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Reviews') }}</div>

        @if ($show->reviews->isEmpty())
            <p class="font-serif text-sm text-tlbx-muted italic">{{ __('No reviews yet. Be the first to review this show.') }}</p>
        @else
            <div class="divide-y divide-tlbx-border">
                @foreach ($show->reviews as $review)
                    <div class="flex gap-3 py-5 first:pt-0">
                        <flux:avatar
                            :name="$review->user?->name ?? __('Deleted user')"
                            :initials="$review->user?->initials() ?? '?'"
                            size="sm"
                        />
                        <div class="flex-1">
                            <div class="flex flex-wrap items-baseline gap-2">
                                <span class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $review->user?->name ?? __('Deleted user') }}
                                </span>
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
</x-layouts::app>