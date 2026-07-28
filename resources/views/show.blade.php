<x-layouts::app :title="$show->name">
    <a href="{{ route('shows.index') }}" class="mb-6 inline-flex items-center gap-1 text-xs tracking-[0.15em] text-tlbx-muted uppercase hover:text-tlbx-primary">
        <flux:icon.arrow-left class="size-3.5" />
        {{ __('Back to shows') }}
    </a>

    <div class="mb-12 flex flex-col gap-8 sm:flex-row">
        <div class="w-40 shrink-0 sm:w-56">
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

        <div class="flex-1">
            <div class="mb-2 text-xs tracking-[0.2em] text-tlbx-muted uppercase">
                {{ $show->first_air_date?->format('Y') ?? __('Unknown year') }}
            </div>

            <h1 class="font-serif text-3xl text-zinc-900 italic sm:text-4xl dark:text-white">
                {{ $show->name }}
            </h1>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <x-star-rating :rating="$show->average_rating" size="size-4" />
                <span class="text-xs text-tlbx-muted">
                    {{ trans_choice(':count review|:count reviews', $show->reviews_count, ['count' => $show->reviews_count]) }}
                </span>
            </div>

            @if ($show->genres->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($show->genres as $genre)
                        <span class="rounded-full border border-tlbx-border px-3 py-1 text-xs text-tlbx-muted">
                            {{ $genre->name }}
                        </span>
                    @endforeach
                </div>
            @endif

            @if ($show->overview)
                <p class="mt-5 max-w-2xl text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                    {{ $show->overview }}
                </p>
            @endif
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
            <div class="flex flex-col gap-5">
                @foreach ($show->reviews as $review)
                    <div class="rounded-md border border-tlbx-border bg-tlbx-card p-4">
                        <div class="flex items-center gap-3">
                            <flux:avatar
                                :name="$review->user?->name ?? __('Deleted user')"
                                :initials="$review->user?->initials() ?? '?'"
                                size="sm"
                            />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $review->user?->name ?? __('Deleted user') }}
                                </div>
                                <div class="text-xs text-tlbx-muted">{{ $review->created_at->diffForHumans() }}</div>
                            </div>
                            <x-star-rating :rating="$review->rating" class="ml-auto" />
                        </div>

                        @if ($review->content)
                            <p class="mt-3 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">
                                {{ $review->content }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts::app>