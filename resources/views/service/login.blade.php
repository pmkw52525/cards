<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <link rel="shortcut icon" type="image/ico" href="img/favicon.ico"/>

	{!! Html::style( asset('css/semantic.min.css') ) !!}
	{!! Html::style( asset('css/icon.min.css') ) !!}
	{!! Html::style( asset('css/style.css') ) !!}
	{!! Html::script( asset('js/jquery.js') ) !!}
	{!! Html::script( asset('js/semantic.min.js') ) !!}

	<title>卡片序號產生系統</title>
</head>
<body class="clearfix">

	<div class="ui main text container content">
		<div class="ui one colusmn stackable center aligned page grid">
		   <div class="column twelve wide">
		   		<h1 class="ui header">卡片序號產生系統</h1>
		   		<div class="ui divider"></div>
				<button class="ui button basic huge red" role="export">以 EIP 帳號登入</button>
		   </div>
		</div>
	</div>
<script>
$(function(){
	$('[role="export"]').click(function(){
		window.location.href = "{!! strtr( env('OAUTH_SERVER'), ['%redirect%' => env('OAUTH_SERVER_REDIRECT'), '%client_id%' => env('OAUTH_CLIENT_ID')]) !!}";
	});
});
</script>

</body>

</html>

