<?php namespace App\Libraries;

use DB, Input;

Class serviceLib {

	public static function getIndexLink() 	{ return route('card.index'); }
	public static function getLogoutLink() 	{ return route('logout'); }
	public static function getLoginLink() 	{ return route('login'); }

}