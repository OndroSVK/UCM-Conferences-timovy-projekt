{{-- Pagination in the prototype look (the Laravel default view needs Tailwind, which is not used here). --}}
@if ($paginator->hasPages())
    <nav class="pagination" aria-label="{{ __('ui.pagination.label') }}">
        @if ($paginator->onFirstPage())
            <span class="btn small flat" aria-disabled="true">{{ __('ui.pagination.previous') }}</span>
        @else
            <a class="btn small flat" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ __('ui.pagination.previous') }}</a>
        @endif

        <span class="muted">{{ __('ui.pagination.page', ['current' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}</span>

        @if ($paginator->hasMorePages())
            <a class="btn small flat" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('ui.pagination.next') }}</a>
        @else
            <span class="btn small flat" aria-disabled="true">{{ __('ui.pagination.next') }}</span>
        @endif
    </nav>
@endif
