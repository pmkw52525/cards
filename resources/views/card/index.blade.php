@extends('main')
@section('content')

<div class="ui text container content">
    <div class="list">
        <div class="ui right aligned grid">
	        <h1>
	        	<i class="icon tag small"></i> 卡片列表
	        	<span class="hint">({{$fromNo}} ~ {{$toNo}})</span>
	        </h1>
	        <!-- <div class="right floated eight wide column">
		    	<div class="ui mini labeled input range">
					<input class="start" type="number" placeholder="流水號" role="range" name="fromNo" value="{{ $fromNo }}">
					<div   class="ui label"> ~ </div>
					<input class="end"   type="number" placeholder="流水號" role="range" name="toNo"   value="{{ $toNo }}">
				</div>
	        </div> -->
	        <div class="right floated four wide column">

		        <select class="ui dropdown" role="activitySlt">
		        	<option value="-1">所有</option>
		        	<option value="0" {!! $activityId == 0 ? 'selected' : '' !!}>未綁定</option>
			        @if ( count($activities) > 0 )
			        	@foreach ($activities as $a)
			        		<option value="{{ $a->id }}" {!! $activityId == $a->id ? 'selected' : '' !!}>{{ $a->title }}</option>
		        		@endforeach
		        	@endif
		        </select>
		    </div>
        	<div class="right floated left aligned three wide column">
        		<button class="ui button basic red" role="export">匯出 Excel</button>

        	</div>
        </div>


<?php
	$title = [
		[ 'title' => '編號'		],
		[ 'title' => '流水號'		],
		[ 'title' => '綁定活動'		],
		[ 'title' => '檢查碼'		],
		[ 'title' => '狀態'		],
	];

	$table = [];
	$table['header'] = $title;

	$data = [];
    foreach ($cards as $c) {
    	$data[] = [
			['data' => $c->id],
			['data' => $c->serialNo],
			['data' => $c->activityId ? $activities[ $c->activityId ]->title : '-' ],
			['data' => $c->code],
			['data' => $c->status == 'enabled' ? '<span class="green"><i class="icon radio"></i>可用</span>' : '<span class="red">'. ( $c->status == 'used' ? '<i class="icon selected radio"></i>已使用' : '<i class="icon remove circle"></i>無效' ) . '</span>'],
    	];
    }

    $table['data'] = $data;
?>
        @include('c.table', $table)
        @include('c.modal', ['title' => '匯出範圍', 'content' => '<div>
        	<div>流水號</div>
        	<span class="ui input"><input type="text" name="start" value="'.$fromNo.'"></span> ~ <span class="ui input"><input type="text" name="end" value="'.$toNo.'"></span>
        </div>'])

		<div class="pager">
	        {{ $cards->appends(['sort' => $sort, 'activityId' => $activityId])->links() }}
		</div>

    </div>
</div>
<script>
$(function(){

	$('[role="activitySlt"]').change(function(){
		submit();
	});

	$('[role="export"]').click(function(){
		$('.ui.modal').modal('show');
	});

	// for modal
	$('[role="ok"]').click(function(){
		$('.ui.modal').modal('hide');
		window.open("{{ route('card.exportExcel') }}" + '?start=' + $('[name="start"]').val() + '&end=' + $('[name="end"]').val() );
	});

	$('[role="range"]').change(function(){
		submit();
	});

	// $('[name="search"]').keypress(function(e){
	// 	if ( e.which == 13 ) {
	// 		submit();
	// 	}
	// });

	// $('i.search').click(function(){
	// 	submit();
	// });

	// $('[role="sort"]').click(function(){
	// 	submit();
	// });

	function submit( param ) {

		// window.location.href = '?search=' + $('[name="search"]').val() + '&fromNo=' + $('[name="fromNo"]').val() + '&toNo=' + $('[name="toNo"]').val();
		// window.location.href = '?fromNo=' + $('[name="fromNo"]').val() + '&toNo=' + $('[name="toNo"]').val();
		window.location.href = '?activityId=' + $('[role="activitySlt"]').val();
	}

});
</script>
@endsection
