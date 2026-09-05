<?php

use Livewire\Component;
use App\Models\Show;
use App\Services\WatchStatsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

new class extends Component {
    public Show $show;
    public bool $isWishlisted = false;

    public function mount(Show $show): void
    {
        $this->show = $show;
        $this->isWishlisted = Auth::user()?->wishlists()->where('show_id', $show->id)->exists() ?? false;
    }

    public function toggle()
    {
        if (!Auth::check()) {
            session()->flash('error', __('You must be logged in to wishlist a show.'));
            return;
        }

        if ($this->isWishlisted) {
            Auth::user()->wishlists()->detach($this->show->id);
            $this->isWishlisted = false;

            return;
        }

        if (app(WatchStatsService::class)->watchedEpisodesCount(Auth::user(), $this->show) > 0) {
            session()->flash('error', __('You can’t add a show you’re already watching to your watchlist.'));
            return;
        }

        Auth::user()->wishlists()->attach($this->show->id);
        $this->isWishlisted = true;
    }

    #[On('watch-progress-updated')]
    public function refreshWishlist(int $showId): void
    {
        if ($showId !== $this->show->id) {
            return;
        }

        $this->isWishlisted = Auth::user()
            ?->wishlists()
            ->where('show_id', $this->show->id)
            ->exists() ?? false;
    }
};
?>

<div
    wire:click="toggle"
    @class([ 'flex items-center gap-2 rounded-sm border px-4 py-2.5 text-sm font-semibold cursor-pointer' , 'border-transparent bg-tlbx-primary/15 text-tlbx-primary'=> $isWishlisted,
    'border-tlbx-border text-zinc-700 dark:text-zinc-200' => ! $isWishlisted,
    ])
    >
    <flux:icon.bookmark :variant="$isWishlisted ? 'solid' : 'outline'" class="size-4" />
    {{ $isWishlisted ? __('In watchlist') : __('Add to watchlist') }}
</div>