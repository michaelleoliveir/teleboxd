<?php

use Livewire\Component;
use App\Models\Review;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public Review $review;
    public bool $liked = false;
    public int $likesCount = 0;

    public function mount(): void
    {
        $this->liked = $this->review->hasAttribute('liked_by_me') 
            ? $this->review->liked_by_me 
            : Auth::check() && $this->review->likes()->where('user_id', Auth::id())->exists();

        $this->likesCount = $this->review->likes_count;
    }

    public function toggle(): void
    {
        if (!Auth::check()) {
            session()->flash('error', __('You must be logged in to like a review.'));
            return;
        }

        $unliked = DB::transaction(function () {
            $reviewLike = $this->review->likes()->where('user_id', Auth::id())->lockForUpdate()->first();

            if ($reviewLike) {
                $reviewLike->delete();
                return true;
            }

            return false;
        });

        if ($unliked) {
            $this->likesCount -= 1;
            $this->liked = false;
        } else {
            $this->liked = true;

            try {
                $this->review->likes()->create([
                    'user_id' => Auth::id()
                ]);

                $this->likesCount += 1;
            } catch (UniqueConstraintViolationException) {
                // A review já tinha sido curtida
            }
        }

        return;
    }
};
?>

<button type="button" wire:click="toggle" wire:loading.attr="disabled" wire:loading.class="opacity-50"
    aria-pressed="{{ $liked ? 'true' : 'false' }}" @class([
        'inline-flex cursor-pointer items-center gap-1.5 rounded-sm text-xs font-semibold transition-colors disabled:cursor-wait',
        'text-tlbx-primary' => $liked,
        'text-tlbx-muted hover:text-tlbx-primary' => !$liked,
    ])>
    <flux:icon.heart :variant="$liked ? 'solid' : 'outline'" class="size-4" />
    <span class="sr-only">{{ __('Like') }}</span>
    <span class="tabular-nums">{{ $likesCount }}</span>
</button>