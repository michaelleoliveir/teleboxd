<?php

use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public User $user;
    public bool $following = false;
    public bool $compact = false;

    public function mount(): void
    {
        $this->following = $this->user->hasAttribute('followed_by_me')
            ? $this->user->followed_by_me
            : Auth::check() && Auth::user()->following()->whereKey($this->user->id)->exists();

    }

    public function toggle(): void
    {
        if (!Auth::check()) {
            session()->flash('error', __('You must be logged in to follow a user.'));
            return;
        }

        if (Auth::id() === $this->user->id) {
            return;
        }

        if ($this->following) {
            Auth::user()->following()->detach($this->user->id);
            
            $this->following = false;
            $this->dispatch('follow-toggled');
            return;
        }

        try {
            Auth::user()->following()->attach($this->user->id);
        } catch (UniqueConstraintViolationException) {
            # user is already followed
        }

        $this->following = true;
        $this->dispatch('follow-toggled');
    }

};
?>

<button type="button" wire:click="toggle" wire:loading.attr="disabled"
    aria-pressed="{{ $following ? 'true' : 'false' }}" @class([
        'group inline-flex cursor-pointer items-center justify-center border transition-colors disabled:cursor-wait disabled:opacity-50',
        'size-8 rounded-full' => $compact,
        'gap-1.5 rounded-sm px-3 py-1 text-[10px] font-semibold tracking-[0.15em] uppercase' => ! $compact,
        'border-transparent bg-tlbx-primary text-white hover:bg-tlbx-border' => $following && $compact,
        'border-transparent bg-tlbx-primary/15 text-tlbx-primary hover:border-tlbx-border hover:bg-transparent hover:text-tlbx-muted' => $following && ! $compact,
        'border-tlbx-border text-zinc-900 hover:border-tlbx-primary hover:text-tlbx-primary dark:text-white' => ! $following,
    ])>
    @if ($compact)
        <span class="sr-only">{{ __('Follow :name', ['name' => $user->name]) }}</span>
        @if ($following)
            <flux:icon.check variant="micro" class="size-5 group-hover:hidden" />
            <flux:icon.x-mark variant="micro" class="hidden size-5 group-hover:block" />
        @else
            <flux:icon.plus variant="micro" class="size-5" />
        @endif
    @elseif ($following)
        <flux:icon.check variant="micro" class="size-3 group-hover:hidden" />
        <span class="group-hover:hidden">{{ __('Following') }}</span>
        <span class="hidden group-hover:inline">{{ __('Unfollow') }}</span>
    @else
        <flux:icon.plus variant="micro" class="size-3" />
        <span>{{ __('Follow') }}</span>
    @endif
</button>