<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\Show;

class ReviewObserver
{
    public function created(Review $review): void
    {
        $this->recalculate($review->show_id);
    }

    public function deleted(Review $review): void
    {
        $this->recalculate($review->show_id);
    }

    public function updated(Review $review): void
    {
        if($review->wasChanged('rating')) {
            $this->recalculate($review->show_id);
        }
    }

    private function recalculate(int $showId): void
    {
        Show::where('id', $showId)->update([
            'average_rating' => Review::where('show_id', $showId)->avg('rating'),
            'reviews_count' => Review::where('show_id', $showId)->count()
        ]);
    }
}
