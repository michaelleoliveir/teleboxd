<?php

use Livewire\Component;
use App\Models\Show;

new class extends Component
{
    public Show $show;
    public bool $isFavorited = false;

    public function mount(Show $show): void
    {
        $this->show = $show;
        $this->isFavorited = auth()->user()?->favorites()->where('show_id', $show->id)->exists() ?? false;
    }

    public function toggle()
    {
        if (auth()->check()) {
            if ($this->isFavorited) {
                auth()->user()->favorites()->detach($this->show->id);
                $this->isFavorited = false;
            } else {
                auth()->user()->favorites()->attach($this->show->id);
                $this->isFavorited = true;
            }
        } else {
            session()->flash('error', __('You must be logged in to favorite a show.'));
        }
    }
};
?>

<div
    wire:click="toggle"
    @class([
        'flex items-center gap-2 rounded-sm border px-4 py-2.5 text-sm font-semibold cursor-pointer',
        'border-transparent bg-tlbx-primary/15 text-tlbx-primary' => $isFavorited,
        'border-tlbx-border text-zinc-700 dark:text-zinc-200' => ! $isFavorited,
    ])
>
    <flux:icon.heart :variant="$isFavorited ? 'solid' : 'outline'" class="size-4" />
    {{ $isFavorited ? __('Favorited') : __('Favorite') }}
</div>