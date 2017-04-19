<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <link rel="shortcut icon" type="image/ico" href="img/favicon.ico"/>

    <script src="js/jquery.js"></script>
    <script src="js/semantic.min.js"></script>
    <link href="css/semantic.min.css" rel="stylesheet">
    <link href="css/icon.min.css" rel="stylesheet" >
    <link href="css/style.css" rel="stylesheet">

	<title>卡片序號產生器</title>
</head>

<body>
	<div class="ui fixed inverted menu">
	  <div class="ui container">
	      	<a href="{{ route('index') }}" class="header item"><i class="home icon"></i> 卡片序號產生器</a>
      		<a href="{{ route('addCards') }}" class="item"><i class="plus icon"></i> 產生序號</a>
    	</div>
	</div>

	<div class="ui main text container">
		@yield('content')
	</div>

<!--   <div class="ui inverted vertical footer segment">
    <div class="ui center aligned container">
        Copyright © 2017 by ELITE International Education Services. All rights reserved.
    </div>
  </div> -->
</body>

</html>
