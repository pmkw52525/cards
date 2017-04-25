<?php

namespace App\Http\Controllers;

use DB, Input;

use App\Libraries\CardLib;

class CardController extends Controller
{

	public function index() {

		$cards  = DB::table('cards')->paginate(15);
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
		$this->createCards( $generateNo, $count, $length, $prefix, json_encode($ext));

		return redirect( route('index') );
	}


	public function getGenerateNo() {

		$card = DB::table('cards')->orderBy('id','asc')->first();

		if ( !isset($card) ) return 0;

		return intval(substr( $card->serialNo , 0, 4)) + 1;
	}


	public function createCards( $generateNo, $count, $length, $prefix = '', $ext = '') {

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


	public function apiGetInfo() {

		$code = trim(Input::get('code'));
		$card = DB::Table('cards')->where('code', $code)->first();

		if ( !$card ) response()->json(['status' => true, 'msg' => 'NO_EXIST_CARD' ]);

		$ext = CardLib::getCardExt( $code );

		return response()->json(['status' => true, 'data' => ['id' => $card->id, 'status' => $card->status, 'ext' => $ext] ]);
	}


	public function apiCheckCard() {

		$code = trim(Input::get('code'));
		$result = CardLib::checkCard($code);

		return response()->json($result);
	}


	public function apiUseCard() {

		$code = trim(Input::get('code'));
		$test = trim(Input::get('test', 0));	// for test mode

		$check = CardLib::checkCard($code);

		if ( !$check['status'] )  return response()->json(['status' => false, 'msg' => $check['msg'] ]);

		if ( $test ) 			  return response()->json(['status' => true]);

		DB::table('cards')->where('code', '=', $code)->update([
				'status'  => CardLib::$STATUS_USED,
				'useTime' => date('Y-m-d H:i:s')
			]);

		$ext = CardLib::getCardExt( $code );

		return response()->json(['status' => true, 'ext' => $ext ]);
	}


}
