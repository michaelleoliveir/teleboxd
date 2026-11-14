<?php

namespace App\Observers;

use App\Models\ReviewLike;

class ReviewLikeObserver
{
    /**
     * Handle the ReviewLike "created" event.
     */
    public function created(ReviewLike $reviewLike): void
    {
        $reviewLike->review->increment('likes_count');
    }

    /**
     * Handle the ReviewLike "deleted" event.
     */
    public function deleted(ReviewLike $reviewLike): void
    {
        $reviewLike->review->decrement('likes_count');
    }
}
