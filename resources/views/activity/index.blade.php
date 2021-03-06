@extends('main')
@section('content')

<div class="content">
    <div class="list">
        <h1><i class="icon flag"></i> 活動列表</h1>
<?php
	$table = [];
	$table['header'] = [
		[ 'title' => '編號'		],
		[ 'title' => '活動名稱'		],
		[ 'title' => '開始日期'		],
		[ 'title' => '結束日期'		],
		[ 'title' => '綁定卡數量'	],
		[ 'title' => 'Referer'	],
		[ 'title' => 'Ext'	],
		// [ 'title' => ''	],
	];

	$data = [];
    foreach ($activities as $a) {
    	$data[] = [
			['data' => $a->id],
			['data' => $a->title],
			['data' => $a->startDate ],
			['data' => $a->endDate],
			['data' => isset($cards[$a->id]) ? count($cards[$a->id]) : '0'],
			['data' => "<span class='link'>".$a->httpReferer."</span>" ],
			['data' => "<div class='ext'>".$a->ext."</div>"],
			// ['data' => "<a href='#' role='addCard'><i class='plus square outline icon'></i></a>"],
    	];
    }

    $table['data'] = $data;
?>
        @include('c.table', $table)
    </div>
</div>
<script>
$(function(){
	$('.link').closest('td').css('max-width', 	'160px');
	$('.link').closest('td').css('overflow', 	'hidden');
	$('.link').closest('td').css('white-space', 'nowrap');
	$('.link').closest('td').css('text-overflow', 'ellipsis');
});
</script>
@endsection
