@props([
    'photo' => null,
    'name',
    'subtitle' => null,
])

<div class="w-32 shrink-0">
    <div class="tlbx-stripe relative aspect-4/5 overflow-hidden rounded-lg border border-tlbx-border shadow-sm">
        @if($photo)
            <img
                src="{{ $photo }}"
                alt="{{ $name }}"
                loading="lazy"
                class="h-full w-full object-cover"
            />
        @endif
    </div>

    <p class="mt-2 truncate text-sm font-bold text-zinc-900 dark:text-white">{{ $name }}</p>
    @if($subtitle)
        <p class="truncate text-xs text-tlbx-muted">{{ $subtitle }}</p>
    @endif
</div>