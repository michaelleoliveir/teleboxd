<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public User $user;

    #[Computed]
    public function followingCount(): int
    {
        return $this->user->following()->count();
    }

    #[Computed]
    public function followersCount(): int
    {
        return $this->user->followers()->count();
    }

    #[On('follow-toggled')]
    public function refresh(): void
    {
        // Follow count is increased when button is toggled 
    }

};
?>

<div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-tlbx-muted">
    <span>
        <span class="font-semibold text-zinc-900 tabular-nums dark:text-white">{{ $this->followingCount }}</span>
        {{ __('following') }}
    </span>
    <span>
        <span class="font-semibold text-zinc-900 tabular-nums dark:text-white">{{ $this->followersCount }}</span>
        {{ trans_choice('follower|followers', $this->followersCount) }}
    </span>
</div>