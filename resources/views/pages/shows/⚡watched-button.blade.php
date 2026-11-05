<?php

use App\Models\Show;
use App\Services\WatchStatsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Show $show;
    public array $progress = [];

    public function mount(Show $show): void
    {
        $this->show = $show;
        $this->loadProgress();
    }

    #[Computed]
    public function episodesWatched(): int
    {
        $stats = app(WatchStatsService::class);
        $user = Auth::user();

        if (!$user) {
            return 0;
        }

        return $stats->watchedEpisodesCount($user, $this->show);
    }

    public function markAllWatched(): void
    {
        foreach ($this->show->seasons as $season) {
            $this->progress[$season->id] = $season->episode_count;
        }
    }

    #[Computed]
    public function currentSeasonId(): ?int
    {
        return $this->show->seasons
            ->first(fn ($season) => (int) ($this->progress[$season->id] ?? 0) < $season->episode_count)
            ?->id;
    }

    public function updateProgress(): void
    {
        if (!Auth::check()) {
            session()->flash('error', __('You must be logged in to mark episodes as watched.'));

            return;
        }

        $rules = [];

        foreach ($this->show->seasons as $season) {
            $rules["progress.{$season->id}"] = ['nullable', 'integer', 'min:0', "max:{$season->episode_count}"];
        }
        $this->validate($rules);

        $saved = $this->savedProgress();
        $toDetach = [];
        $toSync = [];

        foreach ($this->show->seasons as $season) {
            $newData = (int) ($this->progress[$season->id] ?? 0);

            if ($newData === 0) {
                if (array_key_exists($season->id, $saved)) {
                    $toDetach[] = $season->id;
                }
            } elseif (($saved[$season->id] ?? null) !== $newData) {
                $toSync[$season->id] = ['last_watched_episode' => $newData];
            }
        }

        DB::transaction(function () use ($toDetach, $toSync) {
            Auth::user()->watchedSeasons()->detach($toDetach);
            Auth::user()->watchedSeasons()->syncWithoutDetaching($toSync);
        });

        if ($toSync !== []) {
            Auth::user()->wishlists()->detach($this->show->id);
        }

        $this->dispatch('watch-progress-updated', showId: $this->show->id);

        $this->modal('watched-' . $this->show->id)->close();
    }

    public function loadProgress(): void
    {
        $saved = $this->savedProgress();

        $this->progress = $this->show->seasons->mapWithKeys(fn($season) => [$season->id => $saved[$season->id] ?? 0])->all();
    }

    protected function savedProgress(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $seasons = Auth::user()->watchedSeasons()->where('show_id', $this->show->id)->get();

        return $seasons->mapWithKeys(fn($season) => [$season->id => $season->pivot->last_watched_episode])->all();
    }
};
?>

<div>
    <flux:modal.trigger name="watched-{{ $show->id }}">
        <div @class([
            'flex items-center gap-2 rounded-sm border px-4 py-2.5 text-sm font-semibold cursor-pointer',
            'border-transparent bg-tlbx-blue/15 text-tlbx-blue' => $this->episodesWatched() > 0,
            'border-tlbx-border text-zinc-700 dark:text-zinc-200' => $this->episodesWatched() === 0,
        ])>
            <flux:icon.check :variant="$this->episodesWatched() > 0 ? 'solid' : 'outline'" class="size-4" />
            {{ __('Watched') }}
            @if ($show->number_of_episodes)
                <span class="text-xs text-tlbx-muted">{{ $this->episodesWatched() }}/{{ $show->number_of_episodes }}</span>
            @endif
        </div>
    </flux:modal.trigger>

    <flux:modal wire:close="loadProgress" name="watched-{{ $show->id }}" class="w-full max-w-md">
        <div class="flex flex-col gap-5">
            <div class="pr-8">
                <div class="text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Watch progress') }}</div>
                <div class="mt-1 font-serif text-2xl text-zinc-900 italic dark:text-white">{{ $show->name }}</div>
            </div>

            <div class="tlbx-scrollrow flex max-h-80 flex-col gap-2 overflow-y-auto pr-1"
                x-intersect="$el.querySelector('[data-current]')?.scrollIntoView({ block: 'nearest' })">
                @foreach ($show->seasons as $season)
                    <div wire:key="season-{{ $season->id }}"
                        x-data="{
                            total: {{ $season->episode_count }},
                            get watched() { return Number($wire.progress[{{ $season->id }}]) || 0 },
                            get complete() { return this.total > 0 && this.watched >= this.total },
                            get percent() { return this.total > 0 ? Math.min(this.watched / this.total * 100, 100) : 0 },
                        }"
                        @if ($season->id === $this->currentSeasonId) data-current @endif
                        @class([
                            'rounded-md border px-3 py-2.5',
                            'border-tlbx-blue/60 bg-tlbx-blue/5' => $season->id === $this->currentSeasonId,
                            'border-tlbx-border' => $season->id !== $this->currentSeasonId,
                        ])>
                        <div class="flex items-center gap-3">
                            <label for="watched-episode-{{ $season->id }}"
                                class="min-w-0 flex-1 truncate text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $season->name ?? __('Season :number', ['number' => $season->season_number]) }}
                            </label>

                            <div class="flex shrink-0 items-center gap-1 text-sm text-tlbx-muted">
                                <input id="watched-episode-{{ $season->id }}" type="number"
                                    wire:model="progress.{{ $season->id }}" min="0" max="{{ $season->episode_count }}"
                                    class="w-10 rounded-sm border border-transparent bg-transparent py-0.5 text-center text-sm text-zinc-900 [appearance:textfield] hover:border-tlbx-border focus:border-tlbx-blue focus:outline-none dark:text-white [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
                                <span>/ {{ $season->episode_count }}</span>
                            </div>

                            <button type="button"
                                x-on:click="$wire.$set('progress.{{ $season->id }}', complete ? 0 : total)"
                                x-bind:aria-pressed="complete"
                                x-bind:class="complete ? 'border-transparent bg-tlbx-primary/15 text-tlbx-primary' : 'border-tlbx-border text-tlbx-muted hover:border-tlbx-primary hover:text-tlbx-primary'"
                                class="flex size-7 shrink-0 cursor-pointer items-center justify-center rounded-full border"
                                title="{{ __('Mark season as watched') }}"
                                aria-label="{{ __('Mark season as watched') }}">
                                <flux:icon.check class="size-3.5" />
                            </button>
                        </div>

                        <div class="mt-2 h-0.5 overflow-hidden rounded-full bg-tlbx-border">
                            <div class="h-full rounded-full transition-all"
                                x-bind:class="complete ? 'bg-tlbx-primary' : 'bg-tlbx-blue'"
                                x-bind:style="{ width: percent + '%' }"></div>
                        </div>

                        @error("progress.{$season->id}")
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between gap-3">
                <button type="button" wire:click="markAllWatched"
                    class="cursor-pointer text-[10px] tracking-[0.15em] text-tlbx-muted uppercase underline-offset-4 hover:text-tlbx-primary hover:underline">
                    {{ __('Mark all as watched') }}
                </button>

                <div class="flex gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" wire:click="updateProgress">{{ __('Save') }}</flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>