<?php

require_once __DIR__ .'/class.log.php';

/*
 * Class Pipe for servers
 *
 */

class PipeDomain {

	function __construct() {
		$this->name = "PIPE-Domain";
	}

	function __destruct() {
		
	}

	private function log( $type, $str ) {
		global $LOG;

		$LOG->write( $this->name, $type, $str );
	}

	function handle_package() {
		global $GW, $DB, $PKG, $ERR;

		switch( $GW->package["action"] ) {
			case "DOMAIN:register":
				// prepare registration
				$secret    = $GW->create_secret();
				$domain_id = $DB->get_var( "SELECT uuid() as uuid;" );

				// check callback file
				$check = $PKG->curl_call( (object) array(
					"domain"   => $PKG->payload->domain,
					"callback" => $PKG->payload->callback,
					"payload"  => array(
						"action" => "register",
						"ticket" => $PKG->payload->ticket
					)
				) );
				// check response code
				if ( $check->code != 200 ) {
					$ERR->code( 522 );
				}
				// check response status
				if ( $check->response->status != 'ACK' ) {
					$ERR->code( 524 );
				}

				// create an entry
				$DB->query( sprintf( "INSERT INTO wl_domain (ID, ip, domain, name, icon, secret) VALUES('%s', '%s', '%s', '%s', '%s', '%s');",
										$domain_id,
										$PKG->domain_record->ip,
										$PKG->payload->domain,
										$PKG->payload->name,
										$PKG->payload->favicon,
										$secret ) );
				// preparing response
				$PKG->response( array(
					"ID"      => $domain_id,
					"secret"  => $secret,
					"domain"  => $PKG->payload->domain,
					"name"    => $PKG->payload->name,
					"favicon" => $PKG->payload->favicon
				) );
				break;
			case "DOMAIN:renew-secret":
				// create new secret
				$secret    = $GW->create_secret();
				$DB->query( sprintf( "UPDATE wl_domain SET secret='%s' WHERE ID='%s';",
										$secret,
										$PKG->payload->ID ) );
				// create authentication
				$authentication = sha1( $PKG->payload->ticket . $PKG->payload->server->secret . $secret );
				// preparing response
				$PKG->response( array(
					"ID"             => $PKG->payload->ID,
					"secret"         => $secret,
					"authentication" => $authentication,
				) );
				break;
			case "DOMAIN:qr-code":
				// variables required
				$qr_code = $GW->uniqid();
				$detail  = $GW->id_details( $qr_code );
				// ticket is valid for certain time
				$starts = date( 'Y-m-d H:i:s' );
				$ends   = date( 'Y-m-d H:i:s A', time() + QR_VALID );
				// preparing response
				$message = array(
					"action"   => $GW->package["action"],
					"function" => $PKG->payload->function,
					"callback" => $PKG->payload->callback,
				);
				// create an entry
				$DB->query( sprintf( "INSERT INTO wl_ticket (ID, starts, ends, domain, message) VALUES('%s', '%s', '%s', '%s', '%s');",
										$detail->db_id,
										$starts,
										$ends,
										$PKG->payload->server->ID,
										json_encode( $message ) ) );
				// preparing response
				$PKG->response( array(
					"qr" => $qr_code
				) );
				break;
			case "DOMAIN:unregister":
				// remove domain and belonging entries
				$DB->batch( array(
					// Remove domain
					sprintf( "DELETE FROM wl_domain WHERE ID='%s';", $PKG->payload->server->ID ),
					// Remove domain related archive
					sprintf( "DELETE FROM wl_ticket WHERE domain='%s';", $PKG->payload->server->ID ),
					// Remove domain related archive
					sprintf( "DELETE FROM wl_archive WHERE domain='%s';", $PKG->payload->server->ID )
				) );
				// preparing response
				$PKG->response( array(
					"domain" => $PKG->payload->server->domain
				) );
				break;
		}
	}

}

?>