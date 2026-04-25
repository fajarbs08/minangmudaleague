<nav aria-label="Navigasi hasil pertandingan" class="lap-summary-card mt--40">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
        <p class="lap-copy mb-0">Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }} hasil pertandingan. Halaman {{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}.</p>

        <div class="d-flex flex-wrap gap-2">
            @if ($paginator->onFirstPage())
                <span class="btn btn-light disabled">Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-light">Prev</a>
            @endif

            @php $previousPaginationPage = null; @endphp
            @foreach ($paginationPages as $page)
                @if (! is_null($previousPaginationPage) && $page - $previousPaginationPage > 1)
                    <span class="btn btn-light disabled">...</span>
                @endif

                @if ($page === $paginator->currentPage())
                    <span aria-current="page" class="btn btn-primary">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="btn btn-light">{{ $page }}</a>
                @endif

                @php $previousPaginationPage = $page; @endphp
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-light">Next</a>
            @else
                <span class="btn btn-light disabled">Next</span>
            @endif
        </div>
    </div>
</nav>
