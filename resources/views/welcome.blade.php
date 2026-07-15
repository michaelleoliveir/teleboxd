<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', ['title' => __('Track TV series you\'ve watched')])
    </head>
    <body class="bg-tlbx-bg antialiased">
        <header class="sticky top-0 z-20 border-b border-tlbx-border/60 bg-tlbx-bg/80 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <a href="{{ route('home') }}" wire:navigate>
                    <x-teleboxd-logo />
                </a>

                @if (Route::has('login'))
                    <nav class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white" wire:navigate>
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white" wire:navigate>
                                {{ __('Sign in') }}
                            </a>

                            <a href="/register" class="rounded-md bg-tlbx-primary px-4 py-2 text-sm font-semibold text-white transition hover:brightness-110" wire:navigate>
                                {{ __('Create account') }}
                            </a>
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <section class="relative flex min-h-160 items-center justify-center overflow-hidden">
            <div class="absolute inset-0 grid grid-cols-8 grid-rows-4 gap-1 opacity-30">
                @foreach ($shows->take(32) as $show)
                    <div class="bg-cover bg-center" style="background-image: url('https://image.tmdb.org/t/p/w300{{ $show->poster_path }}')"></div>
                @endforeach
            </div>
            <div class="absolute inset-0 bg-linear-to-b from-tlbx-bg/70 via-tlbx-bg/85 to-tlbx-bg"></div>

            <div class="relative mx-auto max-w-3xl px-6 text-center">
                <h1 class="text-4xl font-bold text-zinc-900 sm:text-5xl dark:text-white">
                    {{ __("Track TV series you've watched.") }}
                </h1>
                <p class="mt-4 text-lg text-tlbx-muted">
                    {{ __("Save shows you want to see. Tell your friends what's good.") }}
                </p>

                <a href="/register" class="mt-8 inline-block rounded-md bg-tlbx-primary px-8 py-3 text-base font-semibold text-white transition hover:brightness-110" wire:navigate>
                    {{ __("Get started — it's free") }}
                </a>
            </div>
        </section>

        <section class="border-y border-tlbx-border/60 bg-tlbx-card/40">
            <div class="mx-auto grid max-w-6xl grid-cols-1 gap-10 px-6 py-16 sm:grid-cols-3">
                <div class="flex flex-col items-center text-center">
                    <span class="flex size-12 items-center justify-center rounded-full bg-tlbx-primary/10 text-tlbx-primary">
                        <flux:icon.tv class="size-6" />
                    </span>
                    <h3 class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('Track your shows') }}</h3>
                    <p class="mt-1 text-sm text-tlbx-muted">{{ __('Keep a diary of every series you\'ve watched.') }}</p>
                </div>

                <div class="flex flex-col items-center text-center">
                    <span class="flex size-12 items-center justify-center rounded-full bg-tlbx-primary/10 text-tlbx-primary">
                        <flux:icon.star class="size-6" />
                    </span>
                    <h3 class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('Rate & Review') }}</h3>
                    <p class="mt-1 text-sm text-tlbx-muted">{{ __('Share your thoughts and see what others think.') }}</p>
                </div>

                <div class="flex flex-col items-center text-center">
                    <span class="flex size-12 items-center justify-center rounded-full bg-tlbx-primary/10 text-tlbx-primary">
                        <flux:icon.heart class="size-6" />
                    </span>
                    <h3 class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('Discover what\'s popular') }}</h3>
                    <p class="mt-1 text-sm text-tlbx-muted">{{ __('See the most-loved reviews from the community.') }}</p>
                </div>
            </div>
        </section>

        @if ($shows->isNotEmpty())
            <section class="mx-auto max-w-6xl px-6 py-16">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-sm font-semibold tracking-wide text-tlbx-muted uppercase">{{ __('Popular this week') }}</h2>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ($shows->take(6) as $show)
                        <x-poster-card
                            :poster="$show->poster_path ? 'https://image.tmdb.org/t/p/w500'.$show->poster_path : null"
                            :title="$show->name"
                            :rating="$show->average_rating"
                        />
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="border-t border-tlbx-border/60 bg-tlbx-card/40">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 py-10 sm:flex-row">
                <x-teleboxd-logo size="sm" />

                <nav class="flex gap-6 text-sm text-tlbx-muted">
                    <a href="#" class="hover:text-zinc-900 dark:hover:text-white">{{ __('About') }}</a>
                    <a href="#" class="hover:text-zinc-900 dark:hover:text-white">{{ __('Contact') }}</a>
                    <a href="#" class="hover:text-zinc-900 dark:hover:text-white">{{ __('Shows') }}</a>
                    <a href="#" class="hover:text-zinc-900 dark:hover:text-white">{{ __('Members') }}</a>
                </nav>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>