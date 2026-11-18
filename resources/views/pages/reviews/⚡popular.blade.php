<?php

use App\Models\Review;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;

new #[Title('Popular Reviews |')] class extends Component
{
    use WithPagination;

    public string $period = 'all';

    #[Computed]
    public function reviews(): LengthAwarePaginator
    {
        $since = match($this->period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => null,
        };

        return Review::query()
            ->when($since, fn($q, $date) => $q->where('created_at', '>=', $date))
            ->with(['user', 'show'])
            ->withLikedBy(Auth::id())
            ->where('likes_count', '>', 0)
            ->orderByDesc('likes_count')
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    public function updated(string $property): void
    {
        if ($property === 'period') {
            $this->resetPage();
        }
    }
};
?>

@php
    $periods = [
        'week' => __('Week'),
        'month' => __('Month'),
        'year' => __('Year'),
        'all' => __('All time'),
    ];
@endphp

<div>
    {{-- Header --}}
    <div class="mb-8 flex flex-wrap items-end justify-between gap-6">
        <div>
            <div class="mb-2 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Community') }}</div>
            <h1 class="font-serif text-3xl text-zinc-900 italic sm:text-4xl dark:text-white">{{ __('Popular reviews') }}</h1>
        </div>

        <div class="text-right">
            <div class="font-serif text-3xl text-zinc-900 tabular-nums dark:text-white">{{ $this->reviews->total() }}</div>
            <div class="text-[10px] tracking-[0.15em] text-tlbx-muted uppercase">
                {{ trans_choice('{1} Review|[0,*] Reviews', $this->reviews->total()) }}
            </div>
        </div>
    </div>

    {{-- Period tabs --}}
    <div class="mb-8 flex gap-5 border-b border-tlbx-border sm:gap-8" role="group" aria-label="{{ __('Filter by period') }}">
        @foreach ($periods as $value => $label)
            <button type="button" wire:click="$set('period', '{{ $value }}')"
                aria-pressed="{{ $period === $value ? 'true' : 'false' }}" @class([
                    '-mb-px cursor-pointer border-b-2 pb-3 text-xs font-semibold tracking-[0.15em] whitespace-nowrap uppercase transition-colors',
                    'border-tlbx-primary text-zinc-900 dark:text-white' => $period === $value,
                    'border-transparent text-tlbx-muted hover:text-zinc-900 dark:hover:text-white' => $period !== $value,
                ])>
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div wire:loading.class="opacity-50" class="transition-opacity">
        @if ($this->reviews->isEmpty())
            <div class="flex flex-col items-start gap-3">
                <p class="font-serif text-sm text-tlbx-muted italic">
                    @if ($period === 'all')
                        {{ __('No liked reviews yet.') }}
                    @else
                        {{ __('No liked reviews in this period.') }}
                    @endif
                </p>

                @if ($period !== 'all')
                    <button type="button" wire:click="$set('period', 'all')"
                        class="cursor-pointer text-[10px] tracking-[0.15em] text-tlbx-muted uppercase underline-offset-4 hover:text-tlbx-primary hover:underline">
                        {{ __('See all time') }}
                    </button>
                @endif
            </div>
        @else
            <div class="divide-y divide-tlbx-border">
                @foreach ($this->reviews as $review)
                    <article wire:key="review-{{ $review->id }}" class="flex gap-4 py-6 first:pt-0">
                        <div class="w-5 shrink-0 text-right font-serif text-sm text-tlbx-muted italic tabular-nums">
                            {{ $this->reviews->firstItem() + $loop->index }}
                        </div>

                        <a href="{{ route('shows.show', $review->show) }}" wire:navigate class="w-16 shrink-0">
                            <div class="aspect-2/3 overflow-hidden rounded-md border border-tlbx-border bg-tlbx-card">
                                @if ($review->show->poster_path)
                                    <img src="https://image.tmdb.org/t/p/w185{{ $review->show->poster_path }}"
                                        alt="{{ $review->show->name }}" loading="lazy" class="h-full w-full object-cover" />
                                @endif
                            </div>
                        </a>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline gap-2">
                                <a href="{{ route('shows.show', $review->show) }}" wire:navigate
                                    class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $review->show->name }}
                                </a>
                                @if ($review->show->first_air_date)
                                    <span class="text-xs text-tlbx-muted">{{ $review->show->first_air_date->format('Y') }}</span>
                                @endif
                                <x-star-rating :rating="$review->rating" />
                                <span class="ml-auto text-xs text-tlbx-muted">{{ $review->created_at->diffForHumans() }}</span>
                            </div>

                            <div class="mt-1.5 flex items-center gap-2 text-xs text-tlbx-muted">
                                @if ($review->user)
                                    <a href="{{ route('users.profile', $review->user) }}" wire:navigate>
                                        <flux:avatar :name="$review->user->name" :initials="$review->user->initials()"
                                            :src="$review->user->avatar_url" size="xs" />
                                    </a>
                                    <span>
                                        {{ __('Review by') }}
                                        <a href="{{ route('users.profile', $review->user) }}" wire:navigate
                                            class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $review->user->name }}</a>
                                    </span>
                                @else
                                    <flux:avatar :name="__('Deleted user')" initials="?" size="xs" />
                                    <span>{{ __('Review by a deleted user') }}</span>
                                @endif
                            </div>

                            @if ($review->content)
                                <div x-data="{ expanded: false, clamped: false }"
                                    x-init="$nextTick(() => clamped = $refs.text.scrollHeight > $refs.text.clientHeight + 1)">
                                    <p x-ref="text" :class="expanded ? '' : 'line-clamp-4'"
                                        class="mt-2 font-serif text-sm leading-relaxed whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $review->content }}</p>

                                    <button type="button" x-show="clamped && ! expanded" style="display: none"
                                        x-on:click="expanded = true"
                                        class="mt-1 cursor-pointer text-[10px] tracking-[0.15em] text-tlbx-muted uppercase underline-offset-4 hover:text-tlbx-primary hover:underline">
                                        {{ __('Read more') }}
                                    </button>
                                </div>
                            @endif

                            <div class="mt-3">
                                <livewire:pages::shows.like-button :review="$review" :key="'like-' . $review->id" />
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $this->reviews->links('partials.pagination') }}
            </div>
        @endif
    </div>
</div>
