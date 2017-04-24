<?php

namespace App\Http\Controllers;

use DB, Input;

class ActivityController extends Controller
{

	public function index() {

		return view('activity.index', [
		]);
	}

}
