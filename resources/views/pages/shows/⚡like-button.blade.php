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
        $this->liked = Auth::check() && $this->review->likes()->where('user_id', Auth::id())->exists();
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

<div>
    {{-- Happiness is not something readymade. It comes from your own actions. - Dalai Lama --}}
</div>