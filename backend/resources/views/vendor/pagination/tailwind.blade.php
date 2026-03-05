@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="tn-pagination">
    {{-- Mobile prev/next --}}
    <div class="tn-page-mobile">
        @if ($paginator->onFirstPage())
            <span class="tn-page-btn disabled">{{ __('pagination.previous') }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="tn-page-btn">{{ __('pagination.previous') }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="tn-page-btn">{{ __('pagination.next') }}</a>
        @else
            <span class="tn-page-btn disabled">{{ __('pagination.next') }}</span>
        @endif
    </div>

    {{-- Desktop pagination --}}
    <div class="tn-page-desktop">
        <p class="tn-page-info">
            Showing
            <span class="tn-page-info-num">{{ $paginator->firstItem() }}</span>
            –
            <span class="tn-page-info-num">{{ $paginator->lastItem() }}</span>
            of
            <span class="tn-page-info-num">{{ $paginator->total() }}</span>
        </p>

        <div class="tn-page-buttons">
            {{-- Prev --}}
            @if ($paginator->onFirstPage())
                <span class="tn-page-arrow disabled" aria-disabled="true">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="tn-page-arrow" aria-label="Previous">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="tn-page-dots">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="tn-page-num active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="tn-page-num" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="tn-page-arrow" aria-label="Next">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </a>
            @else
                <span class="tn-page-arrow disabled" aria-disabled="true">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </span>
            @endif
        </div>
    </div>
</nav>

<style>
.tn-pagination { margin-top: 1.5rem; }

/* Mobile */
.tn-page-mobile {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
}
@media (min-width: 640px) { .tn-page-mobile { display: none; } }

/* Desktop */
.tn-page-desktop {
  display: none;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}
@media (min-width: 640px) { .tn-page-desktop { display: flex; } }

/* Info text */
.tn-page-info { font-size: 0.8rem; color: var(--text-muted); }
.tn-page-info-num { font-weight: 600; color: var(--text-subtle); }

/* Button row */
.tn-page-buttons { display: flex; align-items: center; gap: 0.25rem; }

/* Mobile btn */
.tn-page-btn {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 8px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  color: var(--text-subtle);
  text-decoration: none;
  transition: all 0.2s;
}
.tn-page-btn:hover:not(.disabled) {
  background: var(--primary);
  border-color: var(--primary);
  color: white;
}
.tn-page-btn.disabled { opacity: 0.4; cursor: default; }

/* Numbered pages */
.tn-page-num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  border: 1px solid var(--border);
  background: var(--bg-elevated);
  color: var(--text-subtle);
  text-decoration: none;
  transition: all 0.2s;
}
.tn-page-num:hover {
  background: rgba(59,130,246,0.12);
  border-color: rgba(59,130,246,0.4);
  color: var(--primary);
}
.tn-page-num.active {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border-color: transparent;
  color: white;
  box-shadow: 0 0 12px var(--primary-glow);
  cursor: default;
}

/* Arrow buttons */
.tn-page-arrow {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--bg-elevated);
  color: var(--text-subtle);
  text-decoration: none;
  transition: all 0.2s;
}
.tn-page-arrow:hover:not(.disabled) {
  background: rgba(59,130,246,0.12);
  border-color: rgba(59,130,246,0.4);
  color: var(--primary);
}
.tn-page-arrow.disabled { opacity: 0.3; cursor: default; }

/* Dots separator */
.tn-page-dots {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  color: var(--text-muted);
  font-size: 0.8rem;
}

/* Light theme overrides */
[data-theme="light"] .tn-page-num,
[data-theme="light"] .tn-page-arrow,
[data-theme="light"] .tn-page-btn {
  background: #F8FAFC;
  border-color: #E2E8F0;
  color: #475569;
}
[data-theme="light"] .tn-page-num:hover,
[data-theme="light"] .tn-page-arrow:hover:not(.disabled),
[data-theme="light"] .tn-page-btn:hover:not(.disabled) {
  background: #EFF6FF;
  border-color: rgba(59,130,246,0.4);
  color: #1D4ED8;
}
[data-theme="light"] .tn-page-num.active { background: linear-gradient(135deg,#3B82F6,#8B5CF6); color: white; }
[data-theme="light"] .tn-page-info { color: #64748B; }
[data-theme="light"] .tn-page-info-num { color: #334155; }
</style>
@endif
