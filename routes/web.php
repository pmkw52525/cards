<?php


Route::get('/', 		'CardController@index')->name('index');
Route::resource('card',		'CardController');
Route::resource('activity',	'ActivityController');

Route::group(['prefix' => 'card', 'as' => 'card.'], function() {
	Route::post('/postCreate', ['as' => 'postCreate', 'uses' => 'CardController@postCreate']);
});

