<x-layouts::app :title="__('Shows')">
    <div class="mb-9 flex flex-wrap items-end justify-between gap-6">
        <div>
            <div class="mb-2 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ now()->format('F j, Y') }}</div>
            <h1 class="font-serif text-3xl text-zinc-900 italic sm:text-4xl dark:text-white">
                {{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}
            </h1>
        </div>

        {{-- Placeholder: watch stats depend on the review/watch-tracking feature (Fase 4), not built yet.
            Kept as illustrative mock numbers until that data is real. --}}
        <div class="flex gap-8 text-right">
            <div>
                <div class="font-serif text-3xl text-zinc-900 dark:text-white">312</div>
                <div class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">{{ __('Episodes') }}</div>
            </div>
            <div>
                <div class="font-serif text-3xl text-zinc-900 dark:text-white">118h</div>
                <div class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">{{ __('Hours') }}</div>
            </div>
            <div>
                <div class="font-serif text-3xl text-zinc-900 dark:text-white">27</div>
                <div class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">{{ __('Completed') }}</div>
            </div>
        </div>
    </div>

    @if ($shows->isNotEmpty())
        <section class="mb-10">
            <div class="mb-4 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Trending') }}</div>

            <div class="tlbx-scrollrow flex gap-5 overflow-x-auto px-1 pb-3">
                @foreach ($shows as $show)
                    <div class="w-32 shrink-0">
                        <x-poster-card
                            :poster="$show->poster_path ? 'https://image.tmdb.org/t/p/w500'.$show->poster_path : null"
                            :title="$show->name"
                            :rating="$show->average_rating"
                        />
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Placeholder: friends activity and "popular with friends" depend on the social layer
        (following, reviews) which doesn't exist yet. Mock content for visual validation only —
        replace with real queries once that feature ships. --}}
    <div class="mb-10 grid grid-cols-1 gap-10 md:grid-cols-2">
        <div>
            <div class="mb-3 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Friends activity') }}</div>
            <div class="flex flex-col gap-3 font-serif text-sm text-zinc-900 dark:text-white">
                <div><span class="italic">Jonas</span> rated <span class="font-bold">Silo</span> ★★★★★</div>
                <div><span class="italic">Renata</span> watched episode 4 of <span class="font-bold">FROM</span></div>
                <div><span class="italic">Pedro</span> commented on <span class="font-bold">The Rookie</span></div>
            </div>
        </div>
        <div>
            <div class="mb-3 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Popular with friends') }}</div>
            <div class="flex items-center">
                <span class="-mr-2 flex size-9 items-center justify-center rounded-full border-2 border-tlbx-bg bg-tlbx-primary text-sm font-bold text-white">M</span>
                <span class="-mr-2 flex size-9 items-center justify-center rounded-full border-2 border-tlbx-bg bg-tlbx-orange text-sm font-bold text-white">J</span>
                <span class="flex size-9 items-center justify-center rounded-full border-2 border-tlbx-bg bg-sky-400 text-sm font-bold text-white">R</span>
                <span class="ml-3 font-serif text-sm text-tlbx-muted italic">{{ __('watching House of the Dragon') }}</span>
            </div>
        </div>
    </div>

    @if ($actors->isNotEmpty())
        <section>
            <div class="mb-4 flex items-baseline justify-between gap-4">
                <div class="text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Trending cast') }}</div>
                <div class="font-serif text-sm text-tlbx-muted italic">{{ __("who's in what you're watching") }}</div>
            </div>

            <div class="tlbx-scrollrow flex gap-6 overflow-x-auto px-1 pb-3">
                @foreach ($actors as $actor)
                    <x-actor-card
                        :photo="$actor->profile_path ? 'https://image.tmdb.org/t/p/w185'.$actor->profile_path : null"
                        :name="$actor->name"
                    />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts::app>