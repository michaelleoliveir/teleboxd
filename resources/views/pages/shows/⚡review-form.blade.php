<?php

use Livewire\Component;
use App\Models\Show;
use App\Models\Review;
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

    public function submit()
    {
        if(Auth::check()) {
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
    }
};
?>

<form wire:submit="submit" class="flex flex-col gap-3">
    <div class="flex items-center gap-1">
        @for ($i = 1; $i <= 5; $i++)
            <button
                type="button"
                wire:click="$set('rating', {{ $i }})"
                class="cursor-pointer"
            >
                <flux:icon.star
                    variant="solid"
                    class="size-6 {{ $i <= $rating ? 'text-tlbx-orange' : 'text-tlbx-muted' }}"
                />
            </button>
        @endfor
    </div>

    <flux:textarea wire:model="content" rows="3" :placeholder="__('Share your thoughts (optional)')" />

    <flux:button variant="primary" type="submit" class="self-start">
        {{ __('Post review') }}
    </flux:button>
</form>
