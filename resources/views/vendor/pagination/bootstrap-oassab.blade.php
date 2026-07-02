@php
    use App\Support\PaginationPerPage;
    use App\Support\PaginationRange;

    $pageRange = $pageRange ?? PaginationRange::pages($paginator);
    $total = $paginator->total();
    $first = $paginator->firstItem();
    $last = $paginator->lastItem();
@endphp

@if ($total > 0)
    <nav class="bootstrap-pagination" aria-label="Paginação">
        <div class="bootstrap-pagination__meta">
            <span>{{ number_format($total, 0, ',', '.') }} registro(s)</span>
            <span class="bootstrap-pagination__dot" aria-hidden="true">&bull;</span>
            @if ($first)
                <span>Mostrando <strong>{{ $first }} – {{ $last }}</strong></span>
            @endif
        </div>

        @if ($paginator->hasPages())
            <div class="bootstrap-pagination__nav">
                <ul class="pagination mb-0">
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link" aria-label="Página anterior">&lsaquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Página anterior">&lsaquo;</a>
                        </li>
                    @endif

                    @foreach ($pageRange as $page)
                        @if ($page === '...')
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link">&hellip;</span>
                            </li>
                        @elseif ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $paginator->url($page) }}" aria-label="Ir para página {{ $page }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Próxima página">&rsaquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link" aria-label="Próxima página">&rsaquo;</span>
                        </li>
                    @endif
                </ul>
            </div>
        @else
            <div class="bootstrap-pagination__nav" aria-hidden="true"></div>
        @endif

        <form method="GET" class="bootstrap-pagination__per-page">
            @foreach (request()->except(['per_page', 'page']) as $key => $value)
                @if (is_array($value))
                    @foreach ($value as $item)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            <label class="bootstrap-pagination__per-page-label" for="per-page-{{ $paginator->getPageName() }}">
                Itens por página
            </label>
            <div class="bootstrap-pagination__select-wrap">
                <select
                    id="per-page-{{ $paginator->getPageName() }}"
                    name="per_page"
                    class="bootstrap-pagination__select"
                    onchange="this.form.submit()"
                >
                    @foreach (PaginationPerPage::OPTIONS as $option)
                        <option value="{{ $option }}" @selected($paginator->perPage() === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </nav>
@endif
