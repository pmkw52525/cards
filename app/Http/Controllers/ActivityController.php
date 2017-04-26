<?php

namespace App\Http\Controllers;

use DB, Input, Request;

use App\Libraries\CardLib,
	App\Libraries\ActivityLib;

use App\Activity,
	App\Card;

class ActivityController extends Controller
{

	public function index() {

		$activities = Activity::get();

		$actCards = [];
		$cardQry = Card::where('activityId', '!=', '0')->get();
		if ($cardQry) {
			foreach ($cardQry as $c) {
				$actCards[ $c->activityId ][] = $c;
			}
		}

		return view('activity.index', [
			'activities' => $activities,
			'cards' 	 => $actCards,
		]);
	}


	public function apiUpdateActivity() {

		$param = Input::get();

		if ( !isset($param['id']) || !$param['id'] ) { return response()->json(['status' => false, 'msg' => ActivityLib::$NO_EXIST_ACTIVITY ]); }

		if ( isset($param['startCard']) && isset($param['endCard']) ) {
			$check = CardLib::updateRange( $param['id'], $param['startCard'], $param['endCard'] );

			if ( !$check['status'] ) { return response()->json(['status' => false, 'msg' => $check['msg']]); }

		}

		if ( isset($param['title']) && !$param['title'] ) { return response()->json(['status' => false, 'msg' => ActivityLib::$EMPTY_TITLE ]); }

		$activityId = $param['id'];
		unset($param['id']);
		unset($param['startCard']);
		unset($param['endCard']);

		if ( isset($param['startDate']) ) $param['startDate'] = date('Y-m-d', strtotime($param['startDate']));
		if ( isset($param['endDate']) )   $param['endDate']   = date('Y-m-d', strtotime($param['endDate']));

		Activity::where('id', '=', $activityId)->update($param);

 		return response()->json(['status' => true]);
	}


	public function apiDeleteActivity() {

		$id = trim(Input::get('id'));

		$activity = Activity::where('id', '=', $id)->first();
		if ( !$activity ) { return response()->json(['status' => false, 'msg' => ActivityLib::$NO_EXIST_ACTIVITY]); }


		$usedCards = Card::where('activityId', '=', $id)->where('status', '=', 'used')->get();

		if ( count($usedCards) > 0 ) {

			$used = [];
			foreach ($usedCards as $c) {
				$used[] = $c->serialNo;
			}

			return response()->json(['status' => false, 'msg' => ActivityLib::$CARD_USED, 'data' =>  join($used, ', ') ]);
		}

		$cardIds = [];
		$cardQry = Card::where('activityId', '=', $id)->get();
		foreach ($cardQry as $c) $cardIds[] = $c->id;

		CardLib::releaseCards($cardIds);
		Activity::where('id', '=', $id)->delete();

		return response()->json(['status' => true]);
	}


	public function apiCreateActivity() {

		$title 		= trim(Input::get('title'));
		$startDate  = trim(Input::get('startDate'));
		$endDate    = trim(Input::get('endDate'));
		$startCard  = trim(Input::get('startCard'));
		$endCard    = trim(Input::get('endCard'));
		$ext   		= trim(Input::get('ext'));

		$check = CardLib::checkRange($startCard, $endCard);

		if ( !$check['status'] ) { return response()->json(['status' => false, 'msg' => $check['msg']]); }

		if ( !$title ) 			 { return response()->json(['status' => false, 'msg' => ActivityLib::$EMPTY_TITLE]);  }

		$referer    = Request::server('HTTP_REFERER') ?: ( Request::ip() ?: Request::server('HTTP_X_FORWARDED_FOR'));

		$activityId = Activity::insertGetId([
			'title' 		=> $title,
			'startDate' 	=> $startDate ? date('Y-m-d', strtotime($startDate)): NULL,
			'endDate' 		=> $endDate   ? date('Y-m-d', strtotime($endDate))  : NULL,
			'httpReferer'	=> $referer ?: '',
			'ext' 			=> $ext ?: '[]',
		]);

		CardLib::bindCard( $activityId, $startCard, $endCard );

		return response()->json(['status' => true, 'id' => $activityId]);
	}

}
