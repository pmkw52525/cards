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
	public static $WRONG_CARD_RANGE		 = 'WRONG_CARD_RANGE';


	// Card Error Msg
	public static $INVALID_CARD  = 'INVALID_CARD';
	public static $UNBIND_CARD 	 = 'UNBIND_CARD';
	public static $OVERDUE 		 = 'OVERDUE';
	public static $NOT_OPEN 	 = 'NOT_OPEN';
	public static $EDM_CARD 	 = 'EDM_CARD';

	public static function getCreateLink() { return route('card.create'); }
	public static function getIndexLink() { return route('card.index'); }

	public static function getActivityExt( $code ) {

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


	public static function checkCard( $code, $isEdm = false ) {

		$card = Card::where('code', '=', $code)->first();

		if ( !$card ) 			  { return ['status' => false, 'msg' => self::$INVALID_CARD]; }

		$ext = json_decode($card->ext, true);
		if ( !$isEdm && isset($ext['edm']) ) { return ['status' => false, 'msg' => self::$EDM_CARD]; }

		if ( !$card->activityId ) { return ['status' => false, 'msg' => self::$UNBIND_CARD];  }

		$act = Activity::where('id', $card->activityId)->first();

		$now         = date('Y-m-d H:i:s');
		$endDateTime = date('Y-m-d 23:59:59', strtotime($act->endDate));

		if ( $act->endDate   && strtotime($now) > strtotime($endDateTime) )    { return ['status' => false, 'msg' => self::$OVERDUE];  }
		if ( $act->startDate && strtotime($now) < strtotime($act->startDate) ) { return ['status' => false, 'msg' => self::$NOT_OPEN];  }

		if ( $card->status != 'enabled' ) return ['status' => false, 'msg' => self::$INVALID_CARD];

		return ['status' => true ];
	}

	public static function findNoExistCard( $start, $end ) {
		$cards = [];
		for ( $i = $start; $i <= $end; $i++ ) {

			$card = Card::where('serialNo', str_pad($i, 10, "0", STR_PAD_LEFT))->first();
			if ( !isset($card->serialNo) ) {
				$cards[] = str_pad($i, 10, "0", STR_PAD_LEFT);
			}
		}

		return $cards;
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

		if ( count($newQry) != ($end - $start + 1) ) {
			$noExistCards = self::findNoExistCard($start, $end);
			return ['status' => false, 'msg' => self::$WRONG_CARD_RANGE, 'wrongCard' => join(',', $noExistCards) ];
		}

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

		$invalidCards = Card::whereBetween('serialNo', [$start, $end])->where('status', '!=', self::$STATUS_ENABLED)->get();
		$bindedCards  = Card::whereBetween('serialNo', [$start, $end])->where('status', '=',  self::$STATUS_ENABLED)->where('activityId', '!=', '0')->get();
		$cards 		  = Card::whereBetween('serialNo', [$start, $end])->where('status', '=',  self::$STATUS_ENABLED)->where('activityId', '=', '0')->get();

		if ( count($bindedCards)  > 0 )	{ return ['status' => false, 'msg' => self::$BINDED_CARDS	]; }
		if ( count($invalidCards) > 0 )	{ return ['status' => false, 'msg' => self::$INVALID_CARDS	]; }

		if ( count($cards) != ( $end - $start + 1) ) 	{
			$noExistCards = self::findNoExistCard($start, $end);
			return ['status' => false, 'msg' => self::$WRONG_CARD_RANGE, 'wrongCard' => join(',', $noExistCards) ];
		}

		return ['status' => true ];
	}

	public static function getGenerateNo() {

		$card = Card::orderBy('serialNo','desc')->first();

		if ( !isset($card) ) return 0;

		return intval(substr( $card->serialNo , 0, 4)) + 1;
	}


	// public static function extendCards( $generateNo, $startNo, $count, $length, $prefix = '') {
	// 	$result = [];

	// 	for ( $i = $startNo; $i < $count; $i++) {

	// 		$checking = true;
	// 		while ($checking) {

	// 			$code = self::getCode( $length );
	// 			$codeExist = Card::where('code', $code)->first();

	// 			if ( $codeExist ) continue;

	// 			Card::insert([
	// 				'serialNo'	 => str_pad($generateNo, 4, "0", STR_PAD_LEFT) . str_pad($i, 6, "0", STR_PAD_LEFT),
	// 				'code' 		 => $prefix . $code,
	// 				'status'	 => 'enabled',
	// 				'ext'	 	 => $ext,
	// 			]);

	// 			$checking = false;
	// 		}
	// 	}
	// }


	public static function createCards( $generateNo, $count, $length, $prefix = '', $ext = '') {

		$result = [];

		for ( $i = 0; $i < $count; $i++) {

			$checking = true;
			while ($checking) {

				$code = self::getCode( $length );
				$codeExist = Card::where('code', $prefix . $code)->first();

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


	public static function getCode( $length ) {

		$letter = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '2', '3', '4', '5', '7', '8'];

		$cnt = count($letter)-1;

		$chars = '';
		for ( $j = 0; $j < $length; $j++ ) {
			$chars .=  $letter[ mt_rand(0, $cnt) ];
		}

		return $chars;
	}


	public static function createEdmMainCard( $activityId ) {

		$extParam = [
			'edm' 		 => 1,
			'main' 		 => 1,
			'activityId' => $activityId
		];

		$generateNo = self::getGenerateNo();
		self::createCards( $generateNo, 1, 7, '', json_encode($extParam));

		$card = Card::where('ext', json_encode($extParam))->first();

		self::bindCard( $activityId, $card->serialNo, $card->serialNo );
		return $card->code;
	}


	public static function createEdmSubCard( $code ) {

		$mainCard = Card::where('code', $code)->first();
		$extData  = json_decode( $mainCard->ext, true );

		if (!isset($extData['edm']) || !isset($extData['main']) || $extData['main'] != 1  ) return;

		$lastCard = Card::where('activityId', $mainCard->activityId)->orderBy('serialNo', 'DESC')->first();

		$newNo 	  = ($lastCard->serialNo % 100000) + 1;
		$prefix	  = substr($lastCard->serialNo, 0, 4);
		$serialNo = $prefix . str_pad( $newNo, 6, "0", STR_PAD_LEFT);

		Card::insert([
			'serialNo'	 => $serialNo,
			'code' 		 => $code .'_'. $newNo,
			'status'	 => CardLib::$STATUS_ENABLED,
			'useTime'	 => date('Y-m-d'),
			'activityId' => $mainCard->activityId,
			'ext'	 	 => json_encode(['edm' => 1, 'activityId' => $mainCard->activityId ])
		]);

		$card = Card::where('serialNo', $serialNo)->first();

		return $card;
	}

}