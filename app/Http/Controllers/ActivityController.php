<?php

namespace App\Http\Controllers;

use DB, Input;

use App\Libraries\CardLib,
	App\Libraries\ActivityLib;


class ActivityController extends Controller
{

	public function index() {

		$activities = DB::table('activities')->get();

		$actCards = [];
		$cardQry = DB::table('cards')->where('activityId', '!=', '0')->get();
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

		$title 		= trim(Input::get('title'));
		$startDate  = trim(Input::get('startDate'));
		$endDate    = trim(Input::get('endDate'));
		$startCard  = trim(Input::get('startCard'));
		$endCard    = trim(Input::get('endCard'));
		$ext   		= trim(Input::get('ext'));




		isset($param['startCard'])
		// isset($param['endCard'])


	}


	public function apiDeleteActivity() {

		$id = trim(Input::get('id'));

		$activity = DB::table('activities')->where('id', '=', $id)->first();
		if ( !$activity ) { return response()->json(['status' => false, 'msg' => ActivityLib::$NO_EXIST_ACTIVITY]); }


		$usedCards = DB::table('cards')->where('activityId', '=', $id)->where('status', '=', 'used')->get();

		if ( count($usedCards) > 0 ) {

			$used = [];
			foreach ($usedCards as $c) {
				$used[] = $c->serialNo;
			}

			return response()->json(['status' => false, 'msg' => 'CARD_USED', 'data' =>  join($used, ', ') ]);
		}

		$cardIds = [];
		$cardQry = DB::table('cards')->where('activityId', '=', $id)->get();
		foreach ($cardQry as $c) $cardIds[] = $c->id;

		CardLib::releaseCards($cardIds);
		DB::table('activities')->where('id', '=', $id)->delete();

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

		$referer    = request()->headers->get('referer');
		$activityId = DB::table('activities')->insertGetId([
			'title' 		=> $title,
			'startDate' 	=> $startDate ?: NULL,
			'endDate' 		=> $endDate ?: NULL,
			'httpReferer'	=> $referer ?: '',
			'ext' 			=> $ext ?: '[]',
		]);

		CardLib::bindCard( $activityId, $startCard, $endCard);

		return response()->json(['status' => true, 'id' => $activityId]);
	}


}
