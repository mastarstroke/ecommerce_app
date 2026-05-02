@if($users->hasPages())
    <nav>
        <ul class="pagination mb-0">
            {{-- Previous Page Link --}}
            @if ($users->onFirstPage())
                <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
            @else
                <li class="page-item"><a class="page-link" href="#" data-page="{{ $users->currentPage() - 1 }}">&laquo;</a></li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                @if ($page == $users->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="#" data-page="{{ $page }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($users->hasMorePages())
                <li class="page-item"><a class="page-link" href="#" data-page="{{ $users->currentPage() + 1 }}">&raquo;</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
            @endif
        </ul>
    </nav>
@endif

<script>
    // Handle pagination clicks
    $('.pagination a').click(function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            loadUsers(page);
        }
    });
</script>