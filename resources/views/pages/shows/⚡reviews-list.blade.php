<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Show;

new class extends Component
{
    public Show $show;

    public function mount(Show $show): void
    {
        $this->show = $show;
    }

    #[On('review-created')]
    public function refresh(): void
    {
        $this->show->load('reviews.user');
    }
};
?>

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