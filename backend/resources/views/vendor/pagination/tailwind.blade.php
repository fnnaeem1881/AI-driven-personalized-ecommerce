@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-6 select-none">

    {{-- Mobile prev/next --}}
    <div class="flex gap-2 sm:hidden">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">‹ Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 transition-colors">‹ Prev</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 transition-colors">Next ›</a>
        @else
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">Next ›</span>
        @endif
    </div>

    {{-- Desktop info text --}}
    <p class="hidden sm:block text-sm text-gray-500">
        Showing
        <span class="font-semibold text-gray-700">{{ $paginator->firstItem() }}</span>
        &ndash;
        <span class="font-semibold text-gray-700">{{ $paginator->lastItem() }}</span>
        of
        <span class="font-semibold text-gray-700">{{ $paginator->total() }}</span>
        results
    </p>

    {{-- Desktop page buttons --}}
    <div class="hidden sm:flex items-center gap-1">
        {{-- Prev arrow --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center w-9 h-9 text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed" aria-disabled="true">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous" class="inline-flex items-center justify-center w-9 h-9 text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex items-center justify-center w-9 h-9 text-sm text-gray-400 select-none">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 text-sm font-bold text-white bg-blue-600 border border-blue-600 rounded-lg shadow-sm" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="Go to page {{ $page }}" class="inline-flex items-center justify-center w-9 h-9 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next arrow --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next" class="inline-flex items-center justify-center w-9 h-9 text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            </a>
        @else
            <span class="inline-flex items-center justify-center w-9 h-9 text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed" aria-disabled="true">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            </span>
        @endif
    </div>
</nav>
@endif
