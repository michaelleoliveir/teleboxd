@props([
    'poster' => null,
    'title',
    'rating' => null,
])

<div class="group">
    <div class="relative aspect-2/3 overflow-hidden rounded-md border border-tlbx-border bg-tlbx-card shadow-sm">
        @if($poster)
            <img
                src="{{ $poster }}"
                alt="{{ $title }}"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
        @else
            <div class="flex h-full w-full items-center justify-center text-xs text-tlbx-muted">
                {{ __('No poster') }}
            </div>
        @endif
    </div>

    <p class="mt-2 truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $title }}</p>

    <x-star-rating :rating="$rating" class="mt-1" />
</div>