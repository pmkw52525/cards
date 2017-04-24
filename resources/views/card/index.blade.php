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
		[ 'title' => '檢查碼'		],
		[ 'title' => '狀態'		],
		// [ 'title' => '活動名稱'	],
		// [ 'title' => '開始日期'	],
		// [ 'title' => '結束日期'	],
	];

	$data = [];
    foreach ($cards as $c) {
    	$data[] = [
			['data' => $c->id],
			['data' => $c->serialNo],
			['data' => $c->code],
			['data' => $c->status],
			 // $activities[ $c->activityId ]->title,
			 // isset($activities[ $c->activityId ]->startDate) ? $activities[ $c->activityId ]->startDate : '-',
			 // isset($activities[ $c->activityId ]->endDate)   ? $activities[ $c->activityId ]->endDate   : '-',
    	];
    }

    $table['data'] = $data;
?>
        @include('c.table', $table)
    </div>
</div>
<script>
$(function(){
});
</script>
@endsection
