<?php

namespace App\Http\Controllers;

use DB, Input, Excel, App;

use App\Libraries\CardLib;

use App\Activity,
	App\Card;

class CardController extends Controller
{

	public function index() {

		$search = trim( Input::get('search'));
		$actId 	= trim( Input::get('actId', 0));
		$status = trim( Input::get('status', ''));
		$sort 	= trim( Input::get('sort', 'id'));
		$asc 	= trim( Input::get('asc', 'asc'));

		$serialNo = [];
		$code = [];
		$param = [];
		if ( $search ) $serialNo[]  = [ 'serialNo', 'LIKE',  '%'.$search.'%' ];
		if ( $search ) $code[]  	= [ 'code', 	 'LIKE',  '%'.$search.'%' ];

		if ( $status ) $param[]   = [ 'status', 	'=', 	$status ];
		if ( $actId )  $param[]   = [ 'actId',  	'=',	$actId ];

		$cards  = Card::where( $param + $serialNo )->orWhere( $param + $code )->orderBy($sort, $asc)->paginate(100);
		$actQry = Activity::get();

		$activities = [];
		foreach ($actQry as $v) {
			$activities[ $v->id ] = $v;
		}

		$first = Card::where( $param + $serialNo )->orWhere( $param + $code )->orderBy('serialNo', 'asc')->first();
		$last  = Card::where( $param + $serialNo )->orWhere( $param + $code )->orderBy('serialNo', 'desc')->first();

		return view('card.index', [
			'cards' 	 => $cards,
			'search' 	 => $search,
			'sort' 	 	 => $sort,
			'asc' 	 	 => $asc,
			'activities' => $activities,
			'fromNo' 	 => isset($first->serialNo) ? $first->serialNo : '0',
			'toNo' 		 => isset($last->serialNo)  ? $last->serialNo  : '0',
		]);
	}

	public function show() {
	}

	public function create() {
		return view('card.create');
	}

	public function exportExcel() {

		$from = trim(Input::get('start'));
		$to   = trim(Input::get('end'));

		$cards  = Card::whereBetween( 'serialNo', [$from, $to] )->orderBy('serialNo', 'asc')->get();
		$actQry = Activity::get();

		$activities = [];
		foreach ($actQry as $v) $activities[ $v->id ] = $v->title;

		$data = [];
		$data[] = ['流水號', '活動', '檢查碼', '狀態'];
		foreach ($cards as $v) {
			$d = [];
			$d[] = $v->serialNo;
			$d[] = $v->activityId ? $activities[ $v->activityId ] : '-';
			$d[] = $v->code;
			$d[] = $v->status == 'enabled' ? '可用' : ( $c->status == 'used' ? '已使用' : '無效');

			$data[] = $d;
		}


		$excel = App::make('excel');

		Excel::create('Cards', function($excel) use ($data) {

		    $excel->sheet('Excel sheet', function($sheet) use ($data) {
		    	$sheet->fromArray($data, null, 'A1', false, false);
		    });

		})->download('xls');
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

		$card = Card::orderBy('id','asc')->first();

		if ( !isset($card) ) return 0;

		return intval(substr( $card->serialNo , 0, 4)) + 1;
	}


	public function createCards( $generateNo, $count, $length, $prefix = '', $ext = '') {

		$result = [];

		for ( $i = 0; $i < $count; $i++) {

			$checking = true;
			while ($checking) {

				$code = $this->getCode( $length );
				$codeExist = Card::where('code', $code)->first();

				if ( $codeExist ) continue;

				Card::insert([
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
		$card = Card::where('code', $code)->first();

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

		$ext = CardLib::getCardExt( $code );

		if ( $test ) 			  return response()->json(['status' => true, 'ext' => $ext ]);

		Card::where('code', '=', $code)->update([
				'status'  => CardLib::$STATUS_USED,
				'useTime' => date('Y-m-d H:i:s')
			]);


		return response()->json(['status' => true, 'ext' => $ext ]);
	}


}
