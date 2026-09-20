@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-wrap items-center justify-center gap-2">
    @foreach ($elements as $element)
        {{-- "Three Dots" Separator --}}
        @if (is_string($element))
            <span class="flex h-9 w-9 items-center justify-center text-sm font-semibold text-gray-400">{{ $element }}</span>
        @endif

        {{-- Array Of Links --}}
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span aria-current="page">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-maroon text-sm font-bold text-white shadow-md ring-2 ring-maroon-200">{{ $page }}</span>
                    </span>
                @else
                    <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-sm font-semibold text-navy transition hover:border-maroon hover:bg-maroon hover:text-white" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                        {{ $page }}
                    </button>
                @endif
            @endforeach
        @endif
    @endforeach
</nav>
@endif
