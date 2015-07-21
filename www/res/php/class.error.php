<?php

Class Error {
	
	function Code( $code = 507, $details = '' ) {
		global $PKG;

		$string	= array(
			// internal responses
			401 => "Can't connect to Database.",
			402 => "Mysql error: ",
			410 => "Malformed package.",
			411 => "Unknown action.",
			// external responses
			501 => "Access denied.",
			502 => "Bad client request.",
			503 => "Couldn't authenticate request.",
			504 => "Couldn't interpret request.",
			505 => "Unrecognized request.",
			507 => "Unknown error.",
			508 => "Code doesn't match action.",
			509 => "Parameters missing.",
			510 => "Bad authentication code.",
			511 => "Bad ticket_id.",
			512 => "Ticket is old.",
			513 => "Ticket does not exist.",
			514 => "Device does not exist.",
			515 => "Device is not active.",
			516 => "Could not resolve domain.",
			517 => "Domain already registered.",
			518 => "Unrecognized domain.",
			519 => "cURL error: ",
			520 => "Domain doesn't respond correct.",
			521 => "Callback file isn't found.",
			522 => "Callback file doesn't respond correct.",
			523 => "Domain does not exist.",
			524 => "Domain does not respond correct.",
			525 => "Domain respond: ticket is invalid.",
		);
		$response = array(
			'error'       => $code,
			'description' => @$string[$code]
		);
		if ( ! empty( $details ) ) {
			$response['details'] = $details;
		}
		$PKG->response( $response );
	}
	
}

$ERR = new Error;

?>