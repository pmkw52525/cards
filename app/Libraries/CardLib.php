<?php namespace App\Libraries;

use DB, Input;

use App\Activity,
	App\Card;

Class CardLib {

	public static $STATUS_ENABLED  = 'enabled';
	public static $STATUS_DISABLED = 'disabled';
	public static $STATUS_USED 	   = 'used';

	// Range Error Msg
	public static $WRONG_START_FORMAT 	 = 'WRONG_START_FORMAT';
	public static $WRONG_END_FORMAT 	 = 'WRONG_END_FORMAT';
	public static $START_BIGGER_THEN_END = 'START_BIGGER_THEN_END';
	public static $BINDED_CARDS 		 = 'BINDED_CARDS';
	public static $INVALID_CARDS 		 = 'INVALID_CARDS';
	public static $NO_EXIST_CARDS		 = 'NO_EXIST_CARDS';


	// Card Error Msg
	public static $INVALID_CARD  = 'INVALID_CARD';
	public static $UNBIND_CARD 	 = 'UNBIND_CARD';
	public static $OVERDUE 		 = 'OVERDUE';
	public static $NOT_OPEN 	 = 'NOT_OPEN';

	// public static function

	public static function getCardExt( $code ) {

		$card = Card::where('code', '=', $code)->first();

		if ( !$card->activityId ) return '[]';

		$act = Activity::where('id', $card->activityId)->first();
		return $act->ext;
	}


	public static function bindCard( $activityId, $start, $end ) {

		$start = str_pad($start, 10, "0", STR_PAD_LEFT);
		$end   = str_pad($end, 10, "0", STR_PAD_LEFT);

		for ( $i = $start; $i <= $end; $i++ ) {
			Card::where('serialNo', '=', $i)->update(['activityId' => $activityId]);
		}
	}


	public static function releaseCards( $cardIds ) {

		Card::whereIn('id', $cardIds)->update([
			'activityId' => '0',
			'status' 	 => self::$STATUS_ENABLED,
			'ext' 	 	 => '[]',
			'useTime' 	 => NULL,
			'created_at' => NULL,
			'updated_at' => NULL
		]);
	}


	public static function checkCard( $code ) {

		$card = Card::where('code', '=', $code)->first();

		if ( !$card ) 			  { return ['status' => false, 'msg' => self::$INVALID_CARD]; }

		if ( !$card->activityId ) { return ['status' => false, 'msg' => self::$UNBIND_CARD];  }

		$act = Activity::where('id', $card->activityId)->first();

		$now = date('Y-m-d H:i:s');

		if ( strtotime($now) > strtotime($act->endDate) ) 	{ return ['status' => false, 'msg' => self::$OVERDUE];  }
		if ( strtotime($now) < strtotime($act->startDate) ) { return ['status' => false, 'msg' => self::$NOT_OPEN];  }

		if ( $card->status != 'enabled' ) return ['status' => false, 'msg' => self::$INVALID_CARD];

		return ['status' => true ];
	}

	public function updateRange( $activityId, $start, $end ) {

		$start = str_pad($start, 10, "0", STR_PAD_LEFT);
		$end   = str_pad($end, 10, "0", STR_PAD_LEFT);

		if ( !is_numeric($start) ) { return ['status' => false, 'msg' => self::$WRONG_START_FORMAT	];  }
		if ( !is_numeric($end) )   { return ['status' => false, 'msg' => self::$WRONG_END_FORMAT		];  }
		if ( $end - $start < 0 )   { return ['status' => false, 'msg' => self::$START_BIGGER_THEN_END];  }

		$invalidCards = Card::whereBetween('serialNo', [$start, $end])->where('status', '!=', 'enabled')->get();

	}

	public static function checkRange( $start, $end ) {

		$start = str_pad($start, 10, "0", STR_PAD_LEFT);
		$end   = str_pad($end, 10, "0", STR_PAD_LEFT);

		if ( !is_numeric($start) ) { return ['status' => false, 'msg' => self::$WRONG_START_FORMAT	];  }
		if ( !is_numeric($end) )   { return ['status' => false, 'msg' => self::$WRONG_END_FORMAT		];  }
		if ( $end - $start < 0 )   { return ['status' => false, 'msg' => self::$START_BIGGER_THEN_END];  }

		$count = $end - $start + 1;

		$invalidCards = Card::whereBetween('serialNo', [$start, $end])->where('status', '!=', 'enabled')->get();
		$bindedCards  = Card::whereBetween('serialNo', [$start, $end])->where('status', '=',  'enabled')->where('activityId', '!=', '0')->get();
		$cards 		  = Card::whereBetween('serialNo', [$start, $end])->where('status', '=',  'enabled')->where('activityId', '=', '0')->get();

		if ( count($bindedCards) > 0 )	{ return ['status' => false, 'msg' => self::$BINDED_CARDS	]; }
		if ( count($invalidCards) > 0 )	{ return ['status' => false, 'msg' => self::$INVALID_CARDS	]; }
		if ( count($cards) != $count ) 	{ return ['status' => false, 'msg' => self::$NO_EXIST_CARDS	]; }

		return ['status' => true ];
	}


}