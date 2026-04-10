@if ($paginator->hasPages())
<nav aria-label="Navigasi halaman" class="my-4">
    <ul class="pagination justify-content-center" style="gap:4px;">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link" style="background:var(--kicau-surface2);border:1px solid var(--kicau-border);color:var(--kicau-text-muted);border-radius:10px;">
                    <i class="bi bi-chevron-left"></i>
                </span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" style="background:var(--kicau-surface2);border:1px solid var(--kicau-border);color:var(--kicau-text);border-radius:10px;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--kicau-primary)'" onmouseout="this.style.borderColor='var(--kicau-border)'">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled">
                    <span class="page-link" style="background:var(--kicau-surface2);border:1px solid var(--kicau-border);color:var(--kicau-text-muted);border-radius:10px;">{{ $element }}</span>
                </li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active">
                            <span class="page-link" style="background:var(--kicau-primary);border:1px solid var(--kicau-primary);color:#fff;border-radius:10px;font-weight:600;">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}" style="background:var(--kicau-surface2);border:1px solid var(--kicau-border);color:var(--kicau-text);border-radius:10px;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--kicau-primary)';this.style.color='var(--kicau-primary)'" onmouseout="this.style.borderColor='var(--kicau-border)';this.style.color='var(--kicau-text)'">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" style="background:var(--kicau-surface2);border:1px solid var(--kicau-border);color:var(--kicau-text);border-radius:10px;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--kicau-primary)'" onmouseout="this.style.borderColor='var(--kicau-border)'">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link" style="background:var(--kicau-surface2);border:1px solid var(--kicau-border);color:var(--kicau-text-muted);border-radius:10px;">
                    <i class="bi bi-chevron-right"></i>
                </span>
            </li>
        @endif
    </ul>
    <p class="text-center mt-1" style="color:var(--kicau-text-muted);font-size:0.8rem;">
        Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} kicauan
    </p>
</nav>
@endif
