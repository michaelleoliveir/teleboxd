<?php

use App\Models\Season;
use App\Models\Show;
use App\Services\WatchStatsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public Show $show;
    public ?int $selectedSeasonId = null;
    public ?int $lastWatchedEpisode = null;

    public function mount(Show $show): void
    {
        $this->show = $show;
        $this->selectedSeasonId = $show->seasons->first()?->id;
        $this->loadProgressForSelectedSeason();
    }

    public function updatedSelectedSeasonId(): void
    {
        $this->loadProgressForSelectedSeason();
    }

    protected function loadProgressForSelectedSeason(): void
    {
        $this->lastWatchedEpisode = Auth::user()
            ?->watchedSeasons()
            ->wherePivot('season_id', $this->selectedSeasonId)
            ->first()
            ?->pivot
            ->last_watched_episode;
    }

    protected function selectedSeason(): ?Season
    {
        return $this->show->seasons->firstWhere('id', $this->selectedSeasonId);
    }

    public function episodesWatched(): int
    {
        $stats = app(WatchStatsService::class);
        $user = Auth::user();

        if (!$user) {
            return 0;
        }

        return $stats->watchedEpisodesCount($user, $this->show);
    }

    public function updateProgress(): void
    {
        if (! Auth::check()) {
            session()->flash('error', __('You must be logged in to mark episodes as watched.'));

            return;
        }

        $season = $this->selectedSeason();

        $this->validate([
            'selectedSeasonId' => 'required|exists:seasons,id',
            'lastWatchedEpisode' => "required|integer|min:0|max:{$season->episode_count}",
        ]);

        Auth::user()->watchedSeasons()->syncWithoutDetaching([
            $this->selectedSeasonId => ['last_watched_episode' => $this->lastWatchedEpisode],
        ]);

        if ($this->lastWatchedEpisode > 0) {
            Auth::user()->wishlists()->detach($this->show->id);
        }

        $this->dispatch('watch-progress-updated', showId: $this->show->id);

        $this->modal('watched-' . $this->show->id)->close();
    }
};
?>

<div>
    <flux:modal.trigger name="watched-{{ $show->id }}">
        <div
            @class([ 'flex items-center gap-2 rounded-sm border px-4 py-2.5 text-sm font-semibold cursor-pointer' , 'border-transparent bg-tlbx-blue/15 text-tlbx-blue'=> $this->episodesWatched() > 0,
            'border-tlbx-border text-zinc-700 dark:text-zinc-200' => $this->episodesWatched() === 0,
            ])
            >
            <flux:icon.check :variant="$this->episodesWatched() > 0 ? 'solid' : 'outline'" class="size-4" />
            {{ __('Watched') }}
            @if ($show->number_of_episodes)
            <span class="text-xs text-tlbx-muted">{{ $this->episodesWatched() }}/{{ $show->number_of_episodes }}</span>
            @endif
        </div>
    </flux:modal.trigger>

    <flux:modal name="watched-{{ $show->id }}" class="max-w-sm">
        <div class="flex flex-col gap-4">
            <div class="text-lg font-semibold text-zinc-900 dark:text-white">
                {{ __('Update watch progress') }}
            </div>

            <flux:select wire:model.live="selectedSeasonId" label="{{ __('Season') }}">
                @foreach ($show->seasons as $season)
                <flux:select.option value="{{ $season->id }}">
                    {{ $season->name ?? __('Season :number', ['number' => $season->season_number]) }}
                </flux:select.option>
                @endforeach
            </flux:select>

            <div>
                <label for="watched-episode-{{ $show->id }}" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    {{ __('Watched up to episode') }}
                </label>
                <div class="flex items-center gap-2">
                    <input
                        id="watched-episode-{{ $show->id }}"
                        type="number"
                        wire:model="lastWatchedEpisode"
                        min="0"
                        max="{{ $this->selectedSeason()?->episode_count }}"
                        class="w-20 rounded-sm border border-tlbx-border bg-transparent px-2 py-1 text-center text-sm text-zinc-900 dark:bg-zinc-900 dark:text-white" />
                    <span class="text-sm text-tlbx-muted">/ {{ $this->selectedSeason()?->episode_count }}</span>
                </div>
                @error('lastWatchedEpisode')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="updateProgress">{{ __('Save') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>