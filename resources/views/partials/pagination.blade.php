@if($paginator->hasPages())
  @php
    $prev = $paginator->currentPage() - 1;
    $next = $paginator->currentPage() + 1;
    $last = $paginator->lastPage();
    $from = $paginator->firstItem();
    $to = $paginator->lastItem();
    $total = $paginator->total();
  @endphp

  <div class="pagination-wrapper">
    <div class="pagination-info">
      Menampilkan {{ $from }}-{{ $to }} dari {{ $total }} data
    </div>

    <div class="pagination-nav">
      {{-- Prev --}}
      @if($prev >= 1)
        <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn pagination-prev" title="Halaman Sebelumnya">
          <i class="fa-solid fa-chevron-left"></i> Sebelumnya
        </a>
      @else
        <span class="pagination-btn pagination-prev pagination-disabled">
          <i class="fa-solid fa-chevron-left"></i> Sebelumnya
        </span>
      @endif

      {{-- Page Numbers --}}
      @if($last <= 7)
        @for($i = 1; $i <= $last; $i++)
          @if($i == $paginator->currentPage())
            <span class="pagination-btn pagination-active">{{ $i }}</span>
          @else
            <a href="{{ $paginator->url($i) }}" class="pagination-btn">{{ $i }}</a>
          @endif
        @endfor
      @else
        {{-- Always show first page --}}
        @if($paginator->currentPage() > 3)
          <a href="{{ $paginator->url(1) }}" class="pagination-btn">1</a>
          @if($paginator->currentPage() > 4)
            <span class="pagination-ellipsis">...</span>
          @endif
        @endif

        {{-- Pages around current --}}
        @for($i = max(1, $paginator->currentPage() - 2); $i <= min($last, $paginator->currentPage() + 2); $i++)
          @if($i == $paginator->currentPage())
            <span class="pagination-btn pagination-active">{{ $i }}</span>
          @else
            <a href="{{ $paginator->url($i) }}" class="pagination-btn">{{ $i }}</a>
          @endif
        @endfor

        {{-- Always show last page --}}
        @if($paginator->currentPage() < $last - 2)
          @if($paginator->currentPage() < $last - 3)
            <span class="pagination-ellipsis">...</span>
          @endif
          <a href="{{ $paginator->url($last) }}" class="pagination-btn">{{ $last }}</a>
        @endif
      @endif

      {{-- Next --}}
      @if($next <= $last)
        <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn pagination-next" title="Halaman Berikutnya">
          Berikutnya <i class="fa-solid fa-chevron-right"></i>
        </a>
      @else
        <span class="pagination-btn pagination-next pagination-disabled">
          Berikutnya <i class="fa-solid fa-chevron-right"></i>
        </span>
      @endif
    </div>
  </div>
@endif
