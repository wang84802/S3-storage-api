@if ($paginator->hasPages())
    <ul class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <p nowrap><li class="disabled"><span>@lang('pagination.previous')</span></li></p>
        @else
            <p nowrap><li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">@lang('pagination.previous')</a></li></p>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <p nowrap><li><a href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a></li></p>
        @else
            <p nowrap><li class="disabled"><span>@lang('pagination.next')</span></li></p>
        @endif
    </ul>
@endif
