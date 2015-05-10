<?php

$_POST['channel'] = 'DEVICE';
$_POST['action']  = $_POST['channel'].':'. $_GET['action'];
$_POST['payload'] = file_get_contents('php://input');

require_once( '../res/php/class.gw.php' );

?>
