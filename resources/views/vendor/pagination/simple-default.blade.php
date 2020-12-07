@if ($paginator->hasPages())
    <ul class="waypoints -primary -pagination">
        {{-- Previous Page Link --}}
        @if (! $paginator->onFirstPage())
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Prev</a></li>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Next &raquo;</a></li>
        @endif
    </ul>
@endif
