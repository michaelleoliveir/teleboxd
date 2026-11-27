<?php

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public User $user;

    #[Locked]
    public string $type;

    #[Computed]
    public function people(): LengthAwarePaginator
    {
        $relation = match($this->type) {
            'following' => $this->user->following(),
            'followers' => $this->user->followers()
        };

        return $relation
            ->withFollowedBy(Auth::id())
            ->withListStats()
            ->orderByPivot('id', 'desc')
            ->paginate(20);
    }

    public function render(): View
    {
        return $this->view()->title($this->user->name . ' · ' . $this->type . ' |');
    }
};
?>

<div>
    <div class="mb-8">
        <div class="mb-2 text-xs tracking-[0.2em] text-tlbx-muted uppercase">{{ __('Profile') }}</div>
        <h1 class="font-serif text-3xl text-zinc-900 italic sm:text-4xl dark:text-white">
            <a href="{{ route('users.profile', $user) }}" wire:navigate class="transition-colors hover:text-tlbx-primary">{{ $user->name }}</a>
        </h1>
    </div>

    <nav class="flex gap-4 border-b border-tlbx-border" aria-label="{{ __('Follow lists') }}">
        @foreach (['following' => __('Following'), 'followers' => __('Followers')] as $value => $label)
            <a href="{{ route('users.follows', ['user' => $user, 'type' => $value]) }}" wire:navigate
                @if ($type === $value) aria-current="page" @endif
                @class([
                    '-mb-px border-b pb-1.5 text-xs tracking-[0.1em] uppercase transition-colors',
                    'border-zinc-900 text-zinc-900 dark:border-white dark:text-white' => $type === $value,
                    'border-transparent text-tlbx-primary hover:text-zinc-900 dark:hover:text-white' => $type !== $value,
                ])>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @if ($this->people->isEmpty())
        <p class="mt-6 font-serif text-sm text-tlbx-muted italic">
            {{ $type === 'following' ? __('Not following anyone yet.') : __('No followers yet.') }}
        </p>
    @else
        @php $columns = 'grid grid-cols-[minmax(0,1fr)_2rem] items-center gap-x-4 sm:grid-cols-[minmax(0,1fr)_4.5rem_4.5rem_4.5rem_2rem]'; @endphp

        <div wire:loading.class="opacity-50" class="divide-y divide-tlbx-border border-b border-tlbx-border transition-opacity mt-4">
            @foreach ($this->people as $person)
                <div wire:key="person-{{ $person->id }}" class="{{ $columns }} py-2.5 text-xs text-tlbx-muted">
                    <a href="{{ route('users.profile', $person) }}" wire:navigate class="group flex min-w-0 items-center gap-2.5">
                        <flux:avatar circle :name="$person->name" :initials="$person->initials()" :src="$person->avatar_url" />
                        <span class="min-w-0">
                            <span class="block truncate text-base leading-tight font-semibold text-zinc-900 transition-colors group-hover:text-tlbx-blue dark:text-white">{{ $person->name }}</span>
                            <span class="block truncate">
                                {{ trans_choice(':count follower|:count followers', $person->followers_count) }}, {{ __('following :count', ['count' => $person->following_count]) }}
                            </span>
                        </span>
                    </a>

                    <span class="flex items-center gap-1.5 tabular-nums max-sm:hidden">
                        <flux:icon.eye variant="micro" class="size-4 text-tlbx-primary" />
                        <span class="sr-only">{{ __('Watched') }}</span>
                        {{ $person->watched_count }}
                    </span>
                    <span class="flex items-center gap-1.5 tabular-nums max-sm:hidden">
                        <flux:icon.pencil-square variant="micro" class="size-4 text-tlbx-blue" />
                        <span class="sr-only">{{ __('Reviews') }}</span>
                        {{ $person->reviews_count }}
                    </span>
                    <span class="flex items-center gap-1.5 tabular-nums max-sm:hidden">
                        <flux:icon.heart variant="micro" class="size-4 text-tlbx-orange" />
                        <span class="sr-only">{{ __('Favorites') }}</span>
                        {{ $person->favorites_count }}
                    </span>

                    @if ($person->id !== \Illuminate\Support\Facades\Auth::id())
                        <livewire:pages::users.follow-button :user="$person" :compact="true" :key="'follow-' . $person->id" />
                    @endif
                </div>
            @endforeach
        </div>

        {{ $this->people->links('partials.pagination') }}
    @endif
</div>