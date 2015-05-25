<?php

require_once __DIR__ ."/res/php/class.db.php";

$resp = $DB->get_row( "SELECT * FROM wl_ticket WHERE ID='3555c1550c4304';" );


print_r( $resp );

?>