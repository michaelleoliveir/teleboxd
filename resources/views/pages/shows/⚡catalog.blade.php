<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Genre;
use App\Models\Show;

new #[Title('All shows')] class extends Component {
    use WithPagination;

    public ?string $search = null;
    public ?int $genre = null;
    public string $sort = 'popular';

    #[Computed]
    public function genres(): Collection
    {
        return Genre::orderBy('name')->get();
    }

    #[Computed]
    public function shows(): LengthAwarePaginator
    {
        $sortTypes = match ($this->sort) {
            'rating' => 'average_rating',
            'recent' => 'first_air_date',
            default => 'popularity'
        };

        return Show::query()
            ->when($this->genre, fn($q, $genreId) => $q->whereHas('genres', fn($q2) => $q2->where('id', $genreId)))
            ->when($this->search, fn($q, $search) => $q->where('name', 'ilike', "%{$search}%"))
            ->orderByRaw("{$sortTypes} DESC NULLS LAST")
            ->paginate(24);
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'genre', 'sort');
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'genre', 'sort'])) {
            $this->resetPage();
        }
    }
};
?>

<div>
    <div class="mb-9 flex flex-wrap items-end justify-between gap-6">
        <div>
            <div class="mb-2 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Catalog') }}</div>
            <h1 class="font-serif text-3xl text-zinc-900 italic sm:text-4xl dark:text-white">{{ __('All shows') }}</h1>
        </div>
    </div>

    <div class="mb-8 flex flex-wrap items-end gap-6">
        <div class="flex flex-col gap-1.5">
            <span class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">{{ __('Search') }}</span>
            <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search shows...')"
                icon="magnifying-glass" autocomplete="off" class="w-56" />
        </div>

        <div class="flex flex-col gap-1.5">
            <span class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">{{ __('Genre') }}</span>
            <flux:select wire:model.live="genre" class="max-w-48">
                <flux:select.option value="">{{ __('All genres') }}</flux:select.option>
                @foreach ($this->genres as $g)
                    <flux:select.option value="{{ $g->id }}">{{ $g->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="flex flex-col gap-1.5">
            <span class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">{{ __('Sort by') }}</span>
            <flux:select wire:model.live="sort" class="max-w-48">
                <flux:select.option value="popular">{{ __('Most popular') }}</flux:select.option>
                <flux:select.option value="rating">{{ __('Highest rated') }}</flux:select.option>
                <flux:select.option value="recent">{{ __('Most recent') }}</flux:select.option>
            </flux:select>
        </div>

        @if ($this->search || $this->genre || $this->sort !== 'popular')
            <button wire:click="clearFilters" type="button"
                class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase underline-offset-4 cursor-pointer hover:text-tlbx-primary hover:underline">
                {{ __('Clear filters') }}
            </button>
        @endif
    </div>

    @if ($this->shows->isEmpty())
        <p class="font-serif text-sm text-tlbx-muted italic">{{ __('No shows found.') }}</p>
    @else
        <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($this->shows as $show)
                <a href="{{ route('shows.show', $show) }}" wire:navigate>
                    <x-poster-card :poster="$show->poster_path ? 'https://image.tmdb.org/t/p/w500' . $show->poster_path : null"
                        :title="$show->name" :rating="$show->average_rating" />
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $this->shows->links('partials.pagination') }}
        </div>
    @endif
</div>