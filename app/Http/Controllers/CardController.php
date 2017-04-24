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


		return view('card.index', [
			'cards' 	 => $cards,
			'activities' => $activities
		]);
	}

	public function show() {
	}

	public function create() {
		return view('card.create');
	}

	public function postCreate() {

		$count 		= trim( Input::get('count')  );
		$prefix 	= trim( Input::get('prefix') );
		$length 	= trim( Input::get('length') );
		$extKey		= Input::get('extKey');
		$extValue	= Input::get('extValue');

		$ext = [];
		if ( $extKey ) {

			for ( $i = 0; $i < count($extKey); $i++ ) {

				if ( !$extKey[$i] ) continue;

				$ext[ $extKey[$i] ] = $extValue[$i];
			}
		}

		$generateNo = $this->getGenerateNo();
		$this->generateCards( $generateNo, $count, $length, $prefix, json_encode($ext));

		return redirect( route('index') );
	}


	public function getGenerateNo() {

		$card = DB::table('cards')->orderBy('id','asc')->first();

		if ( !isset($card) ) return 0;

		return intval(substr( $card->serialNo , 0, 4)) + 1;
	}


	public function generateCards( $generateNo, $count, $length, $prefix = '', $ext = '') {

		$result = [];

		for ( $i = 0; $i < $count; $i++) {

			$checking = true;
			while ($checking) {

				$code = $this->getCode( $length );
				$codeExist = DB::table('cards')->where('code', $code)->first();

				if ( $codeExist ) continue;

				DB::table('cards')->insert([
					'serialNo'	 => str_pad($generateNo, 4, "0", STR_PAD_LEFT) . str_pad($i, 6, "0", STR_PAD_LEFT),
					'code' 		 => $prefix . $code,
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


	// public function apiCheckRange() {

	// 	$start = trim(Input::get('start'));
	// 	$end   = trim(Input::get('end'));

	// 	$result = $this->checkRange( $start, $end );

	// 	return response()->json($result);
	// }


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


	public function checkRange( $start, $end ) {

		$start = str_pad($start, 10, "0", STR_PAD_LEFT);
		$end   = str_pad($end, 10, "0", STR_PAD_LEFT);

		if ( !is_numeric($start) ) { return ['status' => false, 'msg' => 'Wrong start format'];  }
		if ( !is_numeric($end) )   { return ['status' => false, 'msg' => 'Wrong end format'];  }
		if ( $end - $start < 0 )   { return ['status' => false, 'msg' => 'End number must bigger then start'];  }

		$count = $end - $start + 1;

		$invalidCards = DB::table('cards')->whereBetween('serialNo', [$start, $end])->where('status', '!=', 'enabled')->get();
		$cards 		  = DB::table('cards')->whereBetween('serialNo', [$start, $end])->where('status', '=',  'enabled')->where('activityId', '=', '0')->get();

		if ( count($invalidCards) > 0 )	{ return ['status' => false, 'msg' => 'Some Cards are invalid']; }
		if ( count($cards) != $count ) 	{ return ['status' => false, 'msg' => 'Some Cards not exist']; }

		return ['status' => true];
	}


	public function apiCreateActivity() {

		$title 		= trim(Input::get('title'));
		$startDate  = trim(Input::get('startDate'));
		$endDate    = trim(Input::get('endDate'));
		$startCard  = trim(Input::get('startCard'));
		$endCard    = trim(Input::get('endCard'));
		$ext   		= trim(Input::get('ext'));


		$check = $this->checkRange($startCard, $endCard);

		if ( !$check['status'] ) { return response()->json(['status' => false, 'msg' => 'invalid card range']); }

		if ( !$title ) 			 { return response()->json(['status' => false, 'msg' => 'empty title']);  }

		$referer = request()->headers->get('referer');
		$activityId = DB::table('activities')->insertGetId([
			'title' 		=> $title,
			'startDate' 	=> $startDate ?: NULL,
			'endDate' 		=> $endDate ?: NULL,
			'httpReferer'	=> $referer ?: '',
			'ext' 			=> $ext ?: '[]',
		]);

		$this->bindCard( $activityId, $startCard, $endCard);

		return response()->json(['status' => true, 'id' => $activityId]);
	}


	public function bindCard( $activityId, $start, $end ) {

		$start = str_pad($start, 10, "0", STR_PAD_LEFT);
		$end   = str_pad($end, 10, "0", STR_PAD_LEFT);

		for ( $i = $start; $i <= $end; $i++ ) {
			DB::table('cards')->where('serialNo', '=', $i)->update(['activityId' => $activityId]);
		}
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
