<?php

require_once __DIR__ .'/class.log.php';
require_once __DIR__ .'/class.db.php';
require_once __DIR__ .'/class.error.php';
require_once __DIR__ .'/class.package.php';
require_once __DIR__ .'/pipe.device.php';
require_once __DIR__ .'/pipe.domain.php';

/*
 * Class Gateway
 *
 */

class Gateway {

	function __construct() {
		$this->name = "Gateway";
	}

	function __destruct() {
		
	}

	function init() {
		global $ERR, $PKG;

		$this->package = $PKG->authenticate();

		if ( empty( $this->package ) ) {
			$ERR->code( 502 );
		}

		$this->channel = @$_POST["channel"];
		switch( $this->channel ) {
			case "DEVICE": $PIPE = new PipeDevice; break;
			case "DOMAIN": $PIPE = new PipeDomain; break;
		}
		$PIPE->handle_package();
	}

	private function log( $type, $str ) {
		global $LOG;

		$LOG->write( $this->name, $type, $str );
	}

	function uniqid( $type = 3 ) {
		return uniqid( "WL:" . $type );
	}

	function id_details( $id ) {
		global $ERR;

		if ( substr( $id, 0, 3 ) != "WL:" ) {
			$ERR->code( 511 );
		}
		$type = array(
			"1" => "self registration",
			"2" => "clone account",
			"3" => "use-once ",
			"4" => "long-term",
			"5" => "message",
			"6" => "inquiry"
		);
		$ret = array(
			"id"      => $id,
			"db_id"   => substr($id, 3),
			"type_id" => substr($id, 3, 1),
			"type"    => $type[ substr($id, 3, 1) ]
		);
		return json_decode( json_encode( $ret ) );
	}

	function create_secret( ) {
		$s1 = "qwertzuiopasdfghjklyxcvbnm";
		$s2 = strtoupper( $s1 );
		$s3 = "1234567890";
		$s4 = $s1 . $s3 . $s2;
		$s5 = substr( str_shuffle( $s1 ), 0, 3 )
			. substr( str_shuffle( $s3 ), 0, 1 )
			. substr( str_shuffle( $s2 ), 0, 2 )
			. substr( str_shuffle( $s4 ), 0, 3 )
			. substr( str_shuffle( $s1 ), 0, 2 )
			. substr( str_shuffle( $s2 ), 0, 3 )
			. substr( str_shuffle( $s3 ), 0, 2 );
		return str_shuffle( $s5 );
	}

}

$GW = new Gateway;

$GW->init();

?>