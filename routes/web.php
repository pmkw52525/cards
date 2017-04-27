<?php

Route::get('/', 		'ServiceController@index')->name('index');
Route::get('/login',	'ServiceController@login')->name('login');
Route::get('/auth', 	'ServiceController@auth')->name('auth');
Route::get('/logout', 	'ServiceController@logout')->name('logout');



Route::group(['middleware' => ['auth']], function() {

	Route::group(['prefix' => 'card', 'as' => 'card.'], function() {
		Route::get('/exportExcel', 	['as' => 'exportExcel', 'uses' => 'CardController@exportExcel']);
		Route::post('/postCreate', 	['as' => 'postCreate', 	'uses' => 'CardController@postCreate']);
	});

	Route::resource('card',		'CardController');
	Route::resource('activity',	'ActivityController');
});