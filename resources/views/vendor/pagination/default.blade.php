@if ($paginator->hasPages())
	<div class="ui buttons">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
		    <button class="ui button disabled"><span>&laquo;</span></button>
        @else
		    <button link="{{ $paginator->previousPageUrl() }}" rel="prev" class="ui button">&laquo;</button>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
			    <button class="ui button disabled"><span>{{ $element }}</span></button>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
				        <button class="ui button active"><span>{{ $page }}</span></button>
                    @else
				        <button link="{{ $url }}" class="ui button">{{ $page }}</button>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
	        <button link="{{ $paginator->nextPageUrl() }}" rel="next" class="ui button">&raquo;</button>
        @else
	        <button class="ui button disabled"><span>&raquo;</span></button>
        @endif
    </div>

<script>
$(function(){
	$('button[link]').click(function(){
		window.location.href = $(this).attr('link');
	});
});


</script>
@endif
