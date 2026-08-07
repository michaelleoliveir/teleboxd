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

            <livewire:pages::shows.rating-summary :show="$show" />

            @if ($show->overview)
                <p class="mt-6 max-w-2xl text-[15px] leading-relaxed text-zinc-700 dark:text-zinc-300">
                    {{ $show->overview }}
                </p>
            @endif

            <div class="mt-6 flex flex-wrap items-start gap-3">
                <livewire:pages::shows.favorite-button :show="$show" />

                <livewire:pages::shows.wishlist-button :show="$show" />
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

    <div class="mb-9 max-w-md">
        <div class="mb-4 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Write a review') }}</div>
        <livewire:pages::shows.review-form :show="$show" />
    </div>

    <livewire:pages::shows.reviews-list :show="$show" />
</x-layouts::app>