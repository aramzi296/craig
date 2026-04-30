@if ($paginator->hasPages())
    <div class="pagination-wrapper" style="display: flex; flex-direction: column; align-items: center; gap: 20px; margin-top: 30px; padding: 20px 0;">
        <div class="pagination-info" style="font-size: 0.85rem; color: #64748b; font-weight: 500; background: #f8fafc; padding: 6px 16px; border-radius: 50px; border: 1px solid #f1f5f9;">
            Menampilkan <span style="font-weight: 700; color: var(--primary);">{{ $paginator->firstItem() }}</span>-{{ $paginator->lastItem() }} dari <span style="font-weight: 700; color: #1e293b;">{{ $paginator->total() }}</span> iklan
        </div>

        <nav role="navigation" aria-label="Pagination" style="display: flex; gap: 8px; align-items: center;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled" style="display: flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 14px; background: #f8fafc; color: #cbd5e1; cursor: not-allowed; border: 1px solid #f1f5f9;">
                    <i class="fa-solid fa-chevron-left" style="font-size: 0.75rem;"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-btn" style="display: flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 14px; background: white; color: #64748b; border: 1px solid #e2e8f0; text-decoration: none; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <i class="fa-solid fa-chevron-left" style="font-size: 0.75rem;"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            <div style="display: flex; gap: 8px; align-items: center;" class="pagination-pages">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span style="color: #cbd5e1; padding: 0 5px; font-weight: 700;">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pagination-page active" style="display: flex; align-items: center; justify-content: center; min-width: 42px; height: 42px; padding: 0 12px; border-radius: 14px; background: var(--primary); color: white; font-weight: 700; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3); border: 1px solid var(--primary);">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="pagination-page" style="display: flex; align-items: center; justify-content: center; min-width: 42px; height: 42px; padding: 0 12px; border-radius: 14px; background: white; color: #475569; border: 1px solid #e2e8f0; text-decoration: none; font-weight: 600; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-btn" style="display: flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 14px; background: white; color: #64748b; border: 1px solid #e2e8f0; text-decoration: none; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                </a>
            @else
                <span class="pagination-btn disabled" style="display: flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 14px; background: #f8fafc; color: #cbd5e1; cursor: not-allowed; border: 1px solid #f1f5f9;">
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                </span>
            @endif
        </nav>
    </div>

    <style>
        .pagination-btn:hover, .pagination-page:not(.active):hover {
            border-color: var(--primary) !important;
            color: var(--primary) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
        }
        @media (max-width: 640px) {
            .pagination-pages {
                display: none !important;
            }
            .pagination-info {
                font-size: 0.75rem !important;
            }
        }
    </style>
@endif
