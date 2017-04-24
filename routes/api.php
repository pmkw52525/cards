<?php

use Illuminate\Http\Request;

Route::group(['middleware' => ['api']], function(){

	Route::post('/createActivity', 'CardController@apiCreateActivity');
	Route::post('/checkCard', 'CardController@apiCheckCard');
	Route::post('/useCard',   'CardController@apiUseCard');
});
