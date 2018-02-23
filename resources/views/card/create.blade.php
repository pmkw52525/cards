@extends('main')

@section('content')

{!! Html::script( asset('js/calendar.min.js') ) !!}
{!! Html::style( asset('css/calendar.min.css') ) !!}

<h1>產生序號</h1>
<div class="ui clearing divider"></div>
	{!! Former::open()->class('ui form')->action( route('card.postCreate') )->method('POST'); !!}

	<!-- 	<div class="required field ten wide">
	        <label>活動名稱</label>
	        <input type="text" name="activity" required>
	    </div> -->

		<div class="required field two wide">
	        <label>張數</label>
	        <input type="number" name="count" required number min="1" max="100000">
	    </div>


		<!-- <div class="field">
	        <label>有效期限</label>
	        <div class="fields">
		        <div class="ui calendar four field wide" role="startDate">
				    <div class="ui input left icon">
				    	<i class="calendar icon"></i>
				    	<input name="startDate" type="text" placeholder="開始日期">
				    </div>
				</div>
				<div class="ui calendar four field wide" role="endDate">
				    <div class="ui input left icon">
				    	<i class="calendar icon"></i>
				    	<input name="endDate" type="text" placeholder="結束日期">
				    </div>
				</div>
	        </div>
	    </div> -->

		<div class="field">
	        <label>檢查碼</label>
			<div>
				<div class="ui labeled input six wide field"><a class="ui label">前綴</a><input type="text" name="prefix" ></div>
				<div class="ui labeled input two wide field"><a class="ui label">字元數</a><input type="number" name="length" required number></div>
			</div>
	    </div>

		<div class="field">
	        <label>額外參數</label>
			<div class="extGroup">
				<div class="ui labeled input six wide field"><a class="ui label">參數名稱</a><input type="text" name="extKey[]"></div>
				<div class="ui labeled input six wide field"><a class="ui label">參數值</a><input type="text" name="extValue[]"></div>
				<div role="addExt" class="ui black button small basic"><i class="icon plus"></i></div>
			</div>
	    </div>

	    <button type="submit" class="ui submit button primary">產生</button>
	{!! former::close() !!}

<script>
$(function(){

	$('.extGroup').on('click', '[role="addExt"]', function(){
		$('.extGroup').append(
			'<div class="ui labeled input six wide field"><a class="ui label">參數名稱</a><input type="text" name="extKey[]"></div>\
			<div class="ui labeled input six wide field"><a class="ui label">參數值</a><input type="text" name="extValue[]"></div>\
			<div role="addExt" class="ui black button basic"><i class="icon plus"></i></div>');
	});

    $('form').submit(function(){
        $('button[type="submit"]').addClass('disabled');
    });

	// $('[role="startDate"]').calendar({
	//   	type: 'date',
	//   	endCalendar: $('[role="endDate"]'),
	//   	formatter: {
	//   		date: function(date) {
	// 			return formatDate(date);
	// 	    }
	// 	}
	// });

	// $('[role="endDate"]').calendar({
	//   	type: 'date',
	//   	startCalendar: $('[role="startDate"]'),
	//   	formatter: {
	//   		date: function(date) {
	// 			return formatDate(date);
	// 	    }
	// 	}
	// });

	// function formatDate(date) {
	// 	if (!date) return '';
	// 	var day   = date.getDate();
	// 	var month = date.getMonth() + 1;
	// 	var year  = date.getFullYear();
	// 	return year + '/' + month + '/' + day;
	// }
});
</script>

@endsection