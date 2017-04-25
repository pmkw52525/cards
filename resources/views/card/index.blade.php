@extends('main')
@section('content')

<div class="content">
    <div class="list">
        <h1><i class="icon tag"></i> 卡片列表</h1>
<?php
	$table = [];
	$table['header'] = [
		[ 'title' => '編號'		],
		[ 'title' => '流水號'		],
		[ 'title' => '綁定活動'		],
		[ 'title' => '檢查碼'		],
		[ 'title' => '狀態'		],
	];

	$data = [];
    foreach ($cards as $c) {
    	$data[] = [
			['data' => $c->id],
			['data' => $c->serialNo],
			['data' => $c->activityId ? '<i class="icon check"></i>'. $activities[ $c->activityId ]->title : '-' ],
			['data' => $c->code],
			['data' => $c->status],
    	];
    }

    $table['data'] = $data;
?>
        @include('c.table', $table)
        {{ $cards->links() }}
    </div>
</div>
<script>
$(function(){
});
</script>
@endsection
