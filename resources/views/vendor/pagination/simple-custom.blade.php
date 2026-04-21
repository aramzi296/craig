@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" style="display: flex; gap: 10px; align-items: center;">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-secondary disabled" style="opacity: 0.5; cursor: not-allowed; padding: 10px 20px; border-radius: 50px;">
                <i class="fa-solid fa-chevron-left"></i> Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary" style="padding: 10px 20px; border-radius: 50px; background: white; border: 1px solid var(--border); color: var(--text);">
                <i class="fa-solid fa-chevron-left"></i> Sebelumnya
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secondary" style="padding: 10px 20px; border-radius: 50px; background: white; border: 1px solid var(--border); color: var(--text);">
                Berikutnya <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <span class="btn btn-secondary disabled" style="opacity: 0.5; cursor: not-allowed; padding: 10px 20px; border-radius: 50px;">
                Berikutnya <i class="fa-solid fa-chevron-right"></i>
            </span>
        @endif
    </nav>
@endif
