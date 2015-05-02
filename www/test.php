<?php

require_once( 'res/php/class.db.php' );

$DB->query( "INSERT INTO wl_user (ID) VALUES (NULL);" );

echo $DB->insert_id;

?>