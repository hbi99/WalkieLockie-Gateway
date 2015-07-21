<?php
/*
 * Class Pipe for devices
 *
 */

class PipeDevice {

	function __construct() {
		$this->name = 'PIPE-device';
	}

	function __destruct() {
		
	}

	private function log( $type, $str ) {
		global $LOG;

		$LOG->write( $this->name, $type, $str );
	}

	function handle_package() {
		global $GW, $DB, $PKG, $ERR;

		switch( $GW->package['action'] ) {
			case 'DEVICE:register':
				// variables needed
				$id     = $GW->uniqid( 1 );
				$detail = $GW->id_details( $id );
				$secret = $GW->create_secret();
				$uuid   = $DB->get_var( "SELECT uuid() as uuid;" );

				// ticket is valid for certain time
				$starts = date( 'Y-m-d H:i:s' );
				$ends   = date( 'Y-m-d H:i:s A', time() + QR_VALID );

				// prepare package to save
				$message = json_encode( array(
					'action' => $GW->package['action'],
					'ID'     => $uuid,
					'secret' => $secret,
				) );

				// add entry to DB
				$DB->query( sprintf( "INSERT INTO wl_ticket (ID, domain, starts, ends, message) ".
										"VALUES('%s', 'GATEWAY', '%s', '%s', '%s');", $detail->db_id, $starts, $ends, $message ) );

				// prepare response to device
				$PKG->response( array(
					'ID'     => $uuid,
					'secret' => $secret,
					'ticket' => $id,
				) );
				break;
			case 'DEVICE:register-ack':
				$detail = $GW->id_details( $PKG->payload->ticket );

				// check if code of right type
				if ( $detail->type_id != '1' ) {
					$ERR->code( 508 );
				}
				// execute queries to complete the transaction
				$DB->batch( array(
					// store used message in archive table
					sprintf( "INSERT INTO wl_archive (id, created, starts, ends, domain, message) 
						SELECT  id, created, starts, ends, domain, message FROM wl_ticket WHERE id='%s';", $detail->db_id ),
					// remove message entry
					sprintf( "DELETE FROM wl_ticket WHERE ID='%s';", $detail->db_id ),
					// update archive
					sprintf( "UPDATE wl_archive SET device='%s' WHERE id='%s';",
									$PKG->initial_msg->ID,
									$detail->db_id ),
					sprintf( "INSERT INTO wl_device (ID, is_active, secret) VALUES ('%s', 1, '%s');",
									$PKG->initial_msg->ID,
									$PKG->initial_msg->secret )
				) );
				// prepare response to device
				$PKG->response( array(
					'code'    => 601,
					'details' => 'ACK'
				) );
				break;
			case "DEVICE:renew-secret":
				// variables needed
				$secret = $GW->create_secret();
				// update device secret
				$DB->query( sprintf( "UPDATE wl_device SET secret='%s' WHERE ID='%s';",
									$secret,
									$PKG->payload->device->ID ) );
				// authenticate response
				$authentication = sha1( $PKG->payload->ticket . $PKG->payload->device->secret . $secret );
				// prepare response to device
				$PKG->response( array(
					'ticket'         => $PKG->payload->ticket,
					'secret'         => $secret,
					"authentication" => $authentication,
				) );
				break;
			case "DEVICE:qr-code":
				$detail = $GW->id_details( $PKG->payload->qr );

				// get the server
				$server = $DB->get_row( sprintf( "SELECT * FROM wl_domain WHERE ID='%s';", $PKG->payload->ticket->domain ) );
				if ( empty( $server ) ) {
					// server does not exist
					$ERR->code( 523 );
				}
				// prepare authentication
				$authentication = sha1( $detail->id . $server->secret );
				// make call
				$call = $PKG->curl_call( (object) array(
					"domain"   => $server->domain,
					"callback" => $PKG->payload->ticket->message->callback,
					"payload"  => array(
						"action"         => "qr-check",
						"qr-code"        => $detail->id,
						"function"       => $PKG->payload->ticket->message->function,
						"authentication" => $authentication,
						// this is temporary
						"secret"         => $server->secret,
					)
				) );
				// authenticate response
				$valid_authentication = sha1( $detail->id . $call->response->response . $server->secret );

				if ( $call->response->response != "ACK" ) {
					// domain respond: ticket is invalid.
					$ERR->code( 525 );
				}
				if ( $valid_authentication != $call->response->authentication ) {
					// domain does not respond correct
					$ERR->code( 524 );
				}

				// execute queries to complete the transaction
				$DB->batch( array(
					// store used message in archive table
					sprintf( "INSERT INTO wl_archive (id, created, starts, ends, domain, message) 
						SELECT  id, created, starts, ends, domain, message FROM wl_ticket WHERE id='%s';", $detail->db_id ),
					// remove message entry
					sprintf( "DELETE FROM wl_ticket WHERE ID='%s';", $detail->db_id ),
					// update archive
					sprintf( "UPDATE wl_archive SET device='%s', domain='%s' WHERE id='%s';",
									$PKG->payload->device->ID,
									$server->ID,
									$detail->db_id ),
				) );

				// prepare response to device
				$PKG->response( array(
					'response' => 'ACK',
				//	'detail'   => $detail,
				//	'server'   => $server,
				//	'ticket'   => $PKG->payload->ticket,
				) );
				break;
			case 'DEVICE:unregister':
				// make device inactive
				$DB->query( sprintf( "UPDATE wl_device SET is_active=0 WHERE ID='%s';", $PKG->payload->device->ID ) );
				// prepare response to device
				$PKG->response( array(
					'code'    => 601,
					'details' => 'ACK'
				) );
				break;
			case 'DEVICE:remove':
				// Execute queries to complete the transaction
				$DB->batch( array(
					// Remove device
					sprintf( "DELETE FROM wl_device WHERE ID='%s';", $PKG->payload->device->ID ),
					// Remove device related archive
					sprintf( "DELETE FROM wl_archive WHERE device='%s';", $PKG->payload->device->ID )
				) );

				// prepare response to device
				$PKG->response( array(
					'code'    => 601,
					'details' => 'ACK'
				) );
				break;
		}
	}

}


?>