<?php

Route::get('/', 		'CardController@index')->name('index');
Route::get('/cardList', 'CardController@index')->name('cardList');
Route::get('/addCards', 'CardController@addCards')->name('addCards');

Route::post('/postAddCards', 'CardController@postAddCards')->name('postAddCards');