<table class="top attached ui basic table">
    <thead>
      	<tr>
      		@foreach ($header as $key=>$value)
      			<th>{!! $value['title'] !!}</th>
        	@endforeach
        </tr>
    </thead>

    <tbody>
    @if ( count($data) > 0 )
        @foreach ($data as $rows)
			<tr>
	        	@foreach ($rows as $row)
					<td>{!! $row['data'] !!}</td>
        		@endforeach
			</tr>
        @endforeach
    @else
    	<tr>
    		<td colspan="7" class="noData">無資料</td>
    	</tr>
    @endif
    </tbody>
</table>