@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-center mt-12 gap-8">
        @if ($paginator->onFirstPage())
            <span class="text-[10px] tracking-[0.15em] text-tlbx-muted/40 uppercase">{{ __('Previous') }}</span>
        @else
            <button
                type="button"
                wire:click="previousPage('{{ $paginator->getPageName() }}')"
                class="cursor-pointer text-[10px] tracking-[0.15em] text-tlbx-muted uppercase hover:text-tlbx-primary"
            >
                {{ __('Previous') }}
            </button>
        @endif

        <div class="flex items-center gap-4">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="text-sm text-tlbx-muted">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}" aria-current="page"
                                class="font-serif text-base text-zinc-900 italic dark:text-white">
                                {{ $page }}
                            </span>
                        @else
                            <button
                                type="button"
                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}"
                                class="cursor-pointer text-sm text-tlbx-muted hover:text-tlbx-primary"
                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                            >
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <button
                type="button"
                wire:click="nextPage('{{ $paginator->getPageName() }}')"
                class="cursor-pointer text-[10px] tracking-[0.15em] text-tlbx-muted uppercase hover:text-tlbx-primary"
            >
                {{ __('Next') }}
            </button>
        @else
            <span class="text-[10px] tracking-[0.15em] text-tlbx-muted/40 uppercase">{{ __('Next') }}</span>
        @endif
    </nav>
@endif
