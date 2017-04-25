<?php

Route::get('/', 		'CardController@index')->name('index');

Route::group(['prefix' => 'card', 'as' => 'card.'], function() {
	Route::get('/exportExcel', 	['as' => 'exportExcel', 'uses' => 'CardController@exportExcel']);
	Route::post('/postCreate', 	['as' => 'postCreate', 	'uses' => 'CardController@postCreate']);
});

Route::resource('card',		'CardController');
Route::resource('activity',	'ActivityController');