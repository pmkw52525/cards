<?php

use Illuminate\Http\Request;

Route::group(['middleware' => ['api']], function(){

	Route::post('/updateActivity', 	'ActivityController@apiUpdateActivity');
	Route::post('/createActivity', 	'ActivityController@apiCreateActivity');
	Route::post('/deleteActivity', 	'ActivityController@apiDeleteActivity');
	Route::post('/activityCards',	'ActivityController@apiActivityCards');

	Route::post('/getCardInfo',		'CardController@apiGetInfo');
	Route::post('/checkCard', 		'CardController@apiCheckCard');
	Route::post('/useCard',   		'CardController@apiUseCard');
	Route::post('/useEdmCard', 		'CardController@apiUseEdmCard');

});
