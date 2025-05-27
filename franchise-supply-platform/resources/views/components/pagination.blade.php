@if($items->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} results
        </div>
        <nav>
            <ul class="pagination justify-content-center align-items-center">
                {{-- First Page --}}
                {{-- Previous --}}
                @if ($items->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link"><i class="fas fa-angle-left"></i></span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $items->previousPageUrl() }}" rel="prev">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                @endif

                {{-- Pages --}}
                @foreach ($items->links()->elements[0] as $page => $url)
                    @if ($page == $items->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($items->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $items->nextPageUrl() }}" rel="next">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link"><i class="fas fa-angle-right"></i></span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@else
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Showing {{ $items->count() }} result{{ $items->count() !== 1 ? 's' : '' }}
        </div>
    </div>
@endif