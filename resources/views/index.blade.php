@extends('main')
@section('content')

<script src="js/semantic.min.js"></script>

<div class="content">
    <div class="list">
        <h1><i class="icon tag"></i> 卡片列表</h1>
        <table class="top attached ui basic table">
	        <thead>
	          	<tr>
	          		<th>編號</th>
	          		<th>活動名稱</th>
	          		<th>開始日期</th>
	          		<th>結束日期</th>
	          		<th>卡號</th>
	          		<th>狀態</th>
	        	</tr>
	        </thead>

	        <tbody>
	        @if ( count($cards) > 0 )
		        @foreach ($cards as $c)
					<tr>
						<td>{{ $c->id }}</td>
						<td>{{ $activities[ $c->activityId ]->title }}</td>
						<td>{!! isset($activities[ $c->activityId ]->startDate) ? $activities[ $c->activityId ]->startDate : '-' !!}</td>
						<td>{!! isset($activities[ $c->activityId ]->endDate)   ? $activities[ $c->activityId ]->endDate   : '-' !!}</td>
						<td>{{ $c->code }}</td>
						<td>{{ $c->status }}</td>
					</tr>
		        @endforeach
		    @else
		    	<tr>
		    		<td colspan="5" class="noData">無資料</td>
		    	</tr>
		    @endif
	        </tbody>
      	</table>
    </div>
</div>
<script>
$(function(){
});
</script>
@endsection
