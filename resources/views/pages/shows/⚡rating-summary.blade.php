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
        $this->show->refresh();
    }
};
?>

<div class="mt-5 flex items-center gap-3">
    <x-star-rating :rating="$show->average_rating" size="size-5" />
    <span class="font-serif text-lg text-zinc-900 dark:text-white">{{ $show->average_rating ?? '—' }}</span>
    <span class="text-xs text-tlbx-muted">
        {{ trans_choice(':count review|:count reviews', $show->reviews_count, ['count' => $show->reviews_count]) }}
    </span>
</div>