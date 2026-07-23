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

    @if($rating !== null)
        <div class="mt-1 flex items-center gap-0.5">
            {{-- TODO(human): renderizar 5 estrelas no total.
                 $rating é um decimal de 0 a 5, em passos de 0.5 (regra do projeto).
                 Estrelas preenchidas até $rating devem usar text-tlbx-orange,
                 as demais text-tlbx-muted. Use <flux:icon.star variant="solid" class="size-3.5" />
                 dentro de um @for ($i = 1; $i <= 5; $i++). Bônus: tratar meia-estrela. --}}
        </div>
    @endif
</div>