<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Show;
use App\Models\Review;
use App\Services\WatchStatsService;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public Show $show;
    public int $rating = 0;
    public string $content = '';

    public function mount(Show $show): void
    {
        $this->show = $show;
    }

    #[Computed]
    public function canReview(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return app(WatchStatsService::class)->watchedEpisodesCount($user, $this->show) >= 1;
    }

    #[On('watch-progress-updated')]
    public function refreshCanReview(int $showId): void
    {
        if ($showId !== $this->show->id) {
            return;
        }

        unset($this->canReview);
    }

    public function submit()
    {
        if (!Auth::check()) {
            return;
        }

        if (app(WatchStatsService::class)->watchedEpisodesCount(Auth::user(), $this->show) < 1) {
            $this->addError('rating', __('You must watch at least one episode of the show to review it.'));
            return;
        }

        $this->validate([
            'rating' => 'required|integer|min:0|max:5',
            'content' => 'nullable|string'
        ]);

        Review::create([
            'user_id' => Auth::id(),
            'show_id' => $this->show->id,
            'content' => $this->content,
            'rating' => $this->rating
        ]);

        $this->dispatch('review-created');
    }
};
?>

<div>
    @if ($this->canReview)
        <form wire:submit="submit" class="flex flex-col gap-3">
            <div class="flex items-center gap-1">
                @for ($i = 1; $i <= 5; $i++)
                    <button
                        type="button"
                        wire:click="$set('rating', {{ $i }})"
                        class="cursor-pointer">
                        <flux:icon.star
                            variant="solid"
                            class="size-6 {{ $i <= $rating ? 'text-tlbx-orange' : 'text-tlbx-muted' }}" />
                    </button>
                @endfor
            </div>
    
            @error('rating')
                <p class="font-serif text-sm text-red-500 italic">{{ $message }}</p>
            @enderror
    
            <flux:textarea wire:model="content" rows="3" :placeholder="__('Share your thoughts (optional)')" />
    
            <flux:button variant="primary" type="submit" class="self-start">
                {{ __('Post review') }}
            </flux:button>
        </form>
    @else
        <p class="font-serif text-sm text-tlbx-muted italic">{{ __('You must watch at least one episode of the show to review it.') }}</p>
    @endif
</div>