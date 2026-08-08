@props([
    'rating' => null,
    'size' => 'size-3.5',
])

@if ($rating !== null)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-0.5']) }}>
        @for ($i = 1; $i <= 5; $i++)
            @if ($i <= $rating)
                <flux:icon.star variant="solid" class="{{ $size }} text-tlbx-orange" />
            @else
                <flux:icon.star variant="solid" class="{{ $size }} text-tlbx-muted" />
            @endif
        @endfor
    </div>
@endif
