<?php

namespace App\Http\Controllers;

use DB, Input, Excel, App, Auth;

use App\Libraries\CardLib;

use App\Activity,
	App\Card;

class CardController extends Controller
{

	public function index() {

// dd(Auth::user()) ;
		$search 	= trim( Input::get('search'));
		$activityId	= trim( Input::get('activityId', -1));
		// $status = trim( Input::get('status', ''));
		$fromNo 	= trim( Input::get('fromNo', ''));
		$toNo 		= trim( Input::get('toNo',   ''));
		$sort 		= trim( Input::get('sort',  'id'));
		$asc 		= trim( Input::get('asc', 'asc'));


		$param = [];

		if ($activityId != -1) {
			$param['activityId'] = $activityId;
		}

		$cards  = Card::where( $param )->orderBy($sort, $asc)->paginate(100);
		$first  = Card::where( $param )->orderBy('serialNo', 'asc')->first();
		$last   = Card::where( $param )->orderBy('serialNo', 'desc')->first();

		$fromNo = isset($first->serialNo) ? $first->serialNo : 0;
		$toNo   = isset($last->serialNo)  ? $last->serialNo : 0;
		// if ($fromNo === '' || $toNo === '' ) {

		// 	$cards  = Card::where( $param + $serialNo )->orWhere( $param + $code )->orderBy($sort, $asc)->paginate(100);
		// 	$first  = Card::where( $param + $serialNo )->orWhere( $param + $code )->orderBy('serialNo', 'asc')->first();
		// 	$last   = Card::where( $param + $serialNo )->orWhere( $param + $code )->orderBy('serialNo', 'desc')->first();

		// 	$fromNo = isset($first->serialNo) ? $first->serialNo : 0;
		// 	$toNo   = isset($last->serialNo)  ? $last->serialNo : 0;
		// }
		// else {
		// 	$fromNo = str_pad($fromNo, 10, "0", STR_PAD_LEFT);
		// 	$toNo   = str_pad($toNo, 10, "0", STR_PAD_LEFT);
		// 	$cards  = Card::whereBetween( 'serialNo', [$fromNo, $toNo])->orderBy($sort, $asc)->paginate(100);
		// }

		$actQry = Activity::get();

		$activities = [];
		foreach ($actQry as $v) {
			$activities[ $v->id ] = $v;
		}

		return view('card.index', [
			'cards' 	 => $cards,
			'search' 	 => $search,
			'sort' 	 	 => $sort,
			'asc' 	 	 => $asc,
			'activities' => $activities,
			'activityId' => $activityId,
			'fromNo' 	 => $fromNo === '' ? '0' : $fromNo,
			'toNo' 	 	 => $toNo   === '' ? '0' : $toNo,
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
		$data[] = ['流水號', '檢查碼'];
		// $data[] = ['流水號', '活動', '檢查碼', '狀態'];
		foreach ($cards as $v) {
			$d = [];
			$d[] = $v->serialNo;
			// $d[] = $v->activityId ? $activities[ $v->activityId ] : '-';
			$d[] = $v->code;
			// $d[] = $v->status == 'enabled' ? '可用' : ( $c->status == 'used' ? '已使用' : '無效');

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

		$generateNo = CardLib::getGenerateNo();
		CardLib::createCards( $generateNo, $count, $length, $prefix, json_encode($ext));

		return redirect( CardLib::getIndexLink() );
	}



	public function apiGetInfo() {

		$code = trim(Input::get('code'));
		$card = Card::where('code', '=', $code)->first();

		if ( !$card )  { return response()->json(['status' => false, 'msg' => CardLib::$NO_EXIST_CARDS ]); }

		$ext = CardLib::getActivityExt( $code );

		return response()->json(['status' => true, 'data' => ['serialNo' => $card->serialNo, 'status' => $card->status, 'ext' => $ext] ]);
	}


	public function apiCheckCard() {

		$code = trim(Input::get('code'));
		$result = CardLib::checkCard($code);

		if ( !$result['status'] ) { return response()->json($result); }

		$card = Card::where('code', '=', $code)->first();
		return response()->json(['status' => true, 'serialNo' => $card->serialNo ]);
	}


	public function apiUseCard() {

		$code = trim(Input::get('code'));
		$test = trim(Input::get('test', 0));	// for test mode

		$check = CardLib::checkCard($code);

		if ( !$check['status'] )  return response()->json(['status' => false, 'msg' => $check['msg'] ]);


		$ext = CardLib::getActivityExt( $code );
		$card = Card::where('code', $code)->first();
		if ( $test ) return response()->json(['status' => true, 'ext' => $ext, 'serialNo' => $card->serialNo ]);


		// for preview, unlimited
		$cardExt = json_decode($card->ext);
		if ( array_key_exists('preview', $cardExt) && $cardExt['preview'] == md5('testerneedit') ) {
			return response()->json(['status' => true, 'ext' => $ext, 'serialNo' => $card->serialNo ]);
		}


		Card::where('code', $code)->update([
				'status'  => CardLib::$STATUS_USED,
				'useTime' => date('Y-m-d H:i:s')
			]);

		return response()->json(['status' => true, 'ext' => $ext, 'serialNo' => $card->serialNo ]);
	}


	public function apiUseEdmCard() {

		$code = trim(Input::get('code'));

		$check = CardLib::checkCard($code, true);
		if ( !$check['status'] )  return response()->json(['status' => false, 'msg' => $check['msg'] ]);

		$mainCard = Card::where('code', $code)->first();
		$card  = CardLib::createEdmSubCard( $code );

		Card::where('code', $card->code)->update([
				'status'  => CardLib::$STATUS_USED,
				'useTime' => date('Y-m-d H:i:s')
			]);

		$ext   = CardLib::getActivityExt( $card->code );

		return response()->json(['status' => true, 'ext' => $ext, 'serialNo' => $card->serialNo, 'code' => $card->code ]);
	}


}
