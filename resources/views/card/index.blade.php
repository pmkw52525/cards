@extends('main')
@section('content')

<div class="content">
    <div class="list">
        <div class="ui right aligned grid">
	        <h1>
	        	<i class="icon tag small"></i> 卡片列表
	        </h1>
        	<div class="right floated left aligned four wide column">
        		<button class="ui button basic red" role="export">匯出 Excel</button>
		    <!-- 	<div class="ui icon input">
					<input class="prompt" type="text" name="search" value="{{$search}}" placeholder="流水號, 檢查碼">
					<i class="search icon"></i>
				</div> -->
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
			['data' => $c->status == 'enabled' ? '<i class="icon radio"></i>可用' : ( $c->status == 'used' ? '<i class="icon selected radio"></i>已使用' : '<i class="icon remove circle"></i>無效' )],
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
	        {{ $cards->appends(['sort' => $sort, 'search' => $search])->links() }}
		</div>

    </div>
</div>
<script>
$(function(){

	$('[role="export"]').click(function(){
		$('.ui.modal').modal('show');
	});

	// for modal
	$('[role="ok"]').click(function(){
		$('.ui.modal').modal('hide');
		window.open("{{ route('card.exportExcel') }}" + '?start=' + $('[name="start"]').val() + '&end=' + $('[name="end"]').val() );
	});

	$('[name="search"]').keypress(function(e){
		if ( e.which == 13 ) {
			submit();
		}
	});

	$('i.search').click(function(){
		submit();
	});

	// $('[role="sort"]').click(function(){
	// 	submit();
	// });

	function submit( param ) {

		window.location.href = "{{ route('index') }}" + '?search=' + $('[name="search"]').val();
	}

});
</script>
@endsection
