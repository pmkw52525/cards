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

	<title>卡片序號產生器</title>
</head>
<body class="clearfix">
	<div class="ui fixed inverted menu">
	  	<div class="ui container">
	      	<a href="{!! App\Libraries\ServiceLib::getIndexLink() !!}" 	class="header item"><i class="home icon"></i> 	 卡片序號產生器</a>
      		<a href="{!! App\Libraries\CardLib::getCreateLink() !!}" 	class="item">		<i class="plus icon"></i> 	 產生序號</a>
      		<a href="{!! App\Libraries\ActivityLib::getIndexLink() !!}" class="item">		<i class="flag icon"></i> 	 活動</a>
      		<a href="{!! App\Libraries\ServiceLib::getLogoutLink() !!}" class="item">		<i class="sign out icon"></i>登出</a>
    	</div>
	</div>

	<div class="ui main text container content">
		@yield('content')
	</div>

  	<div class="ui inverted vertical footer segment">
  		<div class="ui center aligned container">Copyright © 2017 by ELITE International Education Services. All rights reserved.</div>
  	</div>
</body>

</html>
