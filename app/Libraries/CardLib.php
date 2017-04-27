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
	public static $USED_CARDS		 	 = 'USED_CARDS';
	public static $INVALID_CARDS 		 = 'INVALID_CARDS';
	public static $NO_EXIST_CARDS		 = 'NO_EXIST_CARDS';


	// Card Error Msg
	public static $INVALID_CARD  = 'INVALID_CARD';
	public static $UNBIND_CARD 	 = 'UNBIND_CARD';
	public static $OVERDUE 		 = 'OVERDUE';
	public static $NOT_OPEN 	 = 'NOT_OPEN';

	public static function getCreateLink() { return route('card.create'); }
	public static function getIndexLink() { return route('card.index'); }

	public static function getCardExt( $code ) {

		$card = Card::where('code', '=', $code)->first();

		if ( !$card->activityId ) return '[]';

		$act = Activity::where('id', $card->activityId)->first();
		return $act->ext;
	}


	public static function bindCard( $activityId, $start, $end ) {

		$start = str_pad($start, 10, "0", STR_PAD_LEFT);
		$end   = str_pad($end,   10, "0", STR_PAD_LEFT);

		Card::whereBetween('serialNo', [$start, $end])->update(['activityId' => $activityId]);
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

	public static function updateRange( $activityId, $start, $end ) {

		$start = str_pad($start, 10, "0", STR_PAD_LEFT);
		$end   = str_pad($end,   10, "0", STR_PAD_LEFT);

		if ( !is_numeric($start) ) { return ['status' => false, 'msg' => self::$WRONG_START_FORMAT	];  }
		if ( !is_numeric($end) )   { return ['status' => false, 'msg' => self::$WRONG_END_FORMAT		];  }
		if ( $end - $start < 0 )   { return ['status' => false, 'msg' => self::$START_BIGGER_THEN_END];  }


		$binded = [];
		$new 	= [];
		$bindedQry 	= Card::where('activityId', $activityId)->get();
		$newQry 	= Card::whereBetween('serialNo', [$start, $end])->get();

		if ( count($newQry) <= 0 ) { return ['status' => false, 'msg' => self::$NO_EXIST_CARDS	];  }

		foreach ($bindedQry as $v) 	{ $binded[ $v->id ] = $v; }
		foreach ($newQry as $v) 	{ $new[ $v->id ] 	= $v; }

		$releaseIds = array_diff( array_keys($binded), 	array_keys($new) );
		$newBindIds = array_diff( array_keys($new), 	array_keys($binded) );


		$releaseCards = Card::whereIn('id', $releaseIds)->get();
		$newBindCards = Card::whereIn('id', $newBindIds)->get();

		$checkCardUsed   = false;
		$checkCardBinded = false;

		if ( count($releaseCards) > 0 ) {
			foreach ( $releaseCards as $v ) {
				if ( $v->status != self::$STATUS_ENABLED ) {
					$checkCardUsed = true;
					break;
				}
			}
		}

		if ( count($newBindCards) > 0 ) {

			foreach ( $newBindCards as $v ) {
				if ( $v->status != self::$STATUS_ENABLED ) {
					$checkCardUsed = true;
					break;
				}

				if ( $v->activityId != 0 ) {

					$checkCardBinded = true;
					break;
				}
			}
		}

		if ( $checkCardUsed )   { return ['status' => false, 'msg' => self::$USED_CARDS	];  }
		if ( $checkCardBinded ) { return ['status' => false, 'msg' => self::$BINDED_CARDS	];  }

		self::releaseCards( $releaseIds );
		self::bindCard( $activityId, $start, $end );

		return ['status' => true];
	}

	public static function checkRange( $start, $end ) {

		$start = str_pad($start, 10, "0", STR_PAD_LEFT);
		$end   = str_pad($end,   10, "0", STR_PAD_LEFT);

		if ( !is_numeric($start) ) { return ['status' => false, 'msg' => self::$WRONG_START_FORMAT	];  }
		if ( !is_numeric($end) )   { return ['status' => false, 'msg' => self::$WRONG_END_FORMAT		];  }
		if ( $end - $start < 0 )   { return ['status' => false, 'msg' => self::$START_BIGGER_THEN_END];  }

		$count = $end - $start + 1;

		$invalidCards = Card::whereBetween('serialNo', [$start, $end])->where('status', '!=', self::$STATUS_ENABLED)->get();
		$bindedCards  = Card::whereBetween('serialNo', [$start, $end])->where('status', '=',  self::$STATUS_ENABLED)->where('activityId', '!=', '0')->get();
		$cards 		  = Card::whereBetween('serialNo', [$start, $end])->where('status', '=',  self::$STATUS_ENABLED)->where('activityId', '=', '0')->get();

		if ( count($bindedCards) > 0 )	{ return ['status' => false, 'msg' => self::$BINDED_CARDS	]; }
		if ( count($invalidCards) > 0 )	{ return ['status' => false, 'msg' => self::$INVALID_CARDS	]; }
		if ( count($cards) != $count ) 	{ return ['status' => false, 'msg' => self::$NO_EXIST_CARDS	]; }

		return ['status' => true ];
	}


}