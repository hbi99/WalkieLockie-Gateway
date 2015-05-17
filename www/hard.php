<?php

require_once __DIR__ ."/res/php/class.error.php";
require_once __DIR__ ."/res/php/class.package.php";

$PKG->payload = (object) array(
	"domain" => "www.defiantjs.com",
	"callback" => "/wl_test.php"
);

$resp = $PKG->check_callback_file( array(
			"pipe"   => "gateway",
			"action" => "validate"
		) );

print_r( $resp );

?>