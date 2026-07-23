@props([
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => ['dot' => 'size-2', 'gap' => 'gap-1', 'text' => 'text-lg'],
        'md' => ['dot' => 'size-2.5', 'gap' => 'gap-1.5', 'text' => 'text-2xl'],
        'lg' => ['dot' => 'size-3', 'gap' => 'gap-2', 'text' => 'text-3xl'],
    ][$size];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-2']) }}>
    <span class="inline-flex items-center {{ $sizes['gap'] }}">
        <span class="{{ $sizes['dot'] }} rounded-full bg-tlbx-primary"></span>
        <span class="{{ $sizes['dot'] }} rounded-full bg-sky-400"></span>
        <span class="{{ $sizes['dot'] }} rounded-full bg-tlbx-orange"></span>
    </span>
    <span class="{{ $sizes['text'] }} font-bold tracking-tight text-zinc-900 dark:text-white">Teleboxd</span>
</span>