<?php

/*
$_POST = array(
	"action"  => "Register_Device",
	"channel" => "app",
	"payload" => "{}"
);
*/

$_POST = array(
	"action"         => "Register_ACK",
	"channel"        => "app",
	"ticket_id"      => "foo-bar",
	"authentication" => "beb9f2efc6d12b5bd15e2e80eeb85156d702107b"
);

require_once( 'res/php/class.gateway.php' );

//$tmp = $DB->get_results( "SELECT * FROM wl_user;" );
//$tmp = $DB->get_var( "SELECT id FROM wl_user WHERE created='2013-11-21 06:07:43';" );

$GW->init();

?>