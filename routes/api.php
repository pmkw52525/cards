<?php

use Illuminate\Http\Request;

Route::middleware('api')->get('/checkCard', 'CardController@apiCheckCard');
Route::middleware('api')->get('/useCard',   'CardController@apiUseCard');
