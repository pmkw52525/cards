<?php

namespace App\Http\Controllers;

use DB, Input;

class CardController extends Controller
{

	public function index() {

		$cards  = DB::table('cards')->get();
		$actQry = DB::table('activities')->get();

		$activities = [];
		foreach ($actQry as $v) {
			$activities[ $v->id ] = $v;
		}

		return view('index', [
			'cards' 	 => $cards,
			'activities' => $activities
		]);
	}

	public function addCards() {
		return view('addCards');
	}

	public function postAddCards() {

		$activity 	= trim( Input::get('activity') );
		$count 		= trim( Input::get('count')  );
		$prefix 	= trim( Input::get('prefix') );
		$length 	= trim( Input::get('length') );
		$startDate 	= trim( Input::get('startDate') );
		$endDate 	= trim( Input::get('endDate') );
		$extKey		= Input::get('extKey');
		$extValue	= Input::get('extValue');

		$activityId = '';
		$act = DB::table('activities')->where('title', '=', $activity)->first();
		if ( !isset($act->id) ) {

			$activityId = DB::table('activities')->insertGetId([
				'title' 	=> $activity,
				'startDate' => $startDate ?: NULL,
				'endDate' 	=> $endDate ?: NULL,
			]);
		}
		else {
			$activityId = $act->id;
		}

		$ext = [];
		if ( $extKey ) {

			for ( $i = 0; $i < count($extKey); $i++ ) {

				if ( !$extKey[$i] ) continue;

				$ext[ $extKey[$i] ] = $extValue[$i];
			}
		}

		$this->generateCards($activityId, $count, $length, $prefix, json_encode($ext));

		return redirect( route('index') );
	}

	public function generateCards( $activityId, $count, $length, $prefix = '', $ext = '') {

		$result = [];

		for ( $i = 0; $i < $count; $i++) {

			$checking = true;
			while ($checking) {

				$code = $this->getCode( $length );
				$codeExist = DB::table('cards')->where('code', $code)->first();

				if ( $codeExist ) continue;

				DB::table('cards')->insert([
					'activityId' => $activityId,
					'code' 		 => $code,
					'status'	 => 'enabled',
					'ext'	 	 => $ext,
				]);

				$checking = false;
			}
		}
	}

	public function getCode( $length ) {

		$letter = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '2', '3', '4', '5', '7', '8'];

		$cnt = count($letter)-1;

		$chars = '';
		for ( $j = 0; $j < $length; $j++ ) {
			$chars .=  $letter[ rand(0, $cnt) ];
		}

		return $chars;
	}

	public function apiCheckCard() {

		$code = trim(Input::get('code'));
		$result = $this->checkCard($code);

		return response()->json($result);
	}

	public function apiUseCard() {

		$code = trim(Input::get('code'));
		$test = trim(Input::get('test', 0));	// for test mode

		$check = $this->checkCard($code);

		if ( !$check['status'] )  return response()->json(['status' => false, 'msg' => $check['msg'] ]);

		if ( $test ) 			  return response()->json(['status' => true]);

		$card = DB::table('cards')->where('code', '=', $code)->update(['status' => 'used']);
		return response()->json(['status' => true]);

	}

	public function checkCard( $code ) {
		$card = DB::table('cards')->where('code', '=', $code)->first();

		if ( !$card ) return ['status' => false, 'msg' => '無此卡號'];

		// date
		// $act = DB::table('activities')->find( $card->activityId );
		// if ( strtotime($act->startDate) )

		if ( $card->status != 'enabled' ) return ['status' => false, 'msg' => '無效卡號'];

		return ['status' => true, 'msg' => 'ok'];
	}
}
