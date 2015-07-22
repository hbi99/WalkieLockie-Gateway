<?php

/*
 * Class Package
 *
 */

class Package {

	function __construct() {
		$this->name = 'PKG';
	}

	function __destruct() {
		
	}

	private function log( $type, $str ) {
		global $LOG;

		$LOG->write( $this->name, $type, $str );
	}

	function response( $payload ) {
		// output response with correct header
		header('Content-type: application/json');
		die( json_encode( $payload ) );
	}

	function get_IP_by_domain( $host, $timeout = 3 ) {
		$ret = array( "host"    => $host );
		$query = `nslookup -timeout=$timeout -retry=1 $host`;
		// collect IP info
		if ( preg_match( '/\nAddress: (.*)\n/', $query, $ip_matches ) ) {
			// collect info about LAN gateway
			preg_match( '/Server:(.*)\n/', $query, $gw_matches );
			$ret["ip"]      = trim( $ip_matches[1] );
			$ret["gateway"] = trim( $gw_matches[1] );
		}
		return (object) $ret;
	}

	function curl_call( $package ) {
		global $ERR;
		// prepare curl call
		$curl = curl_init();
		curl_setopt_array( $curl, array(
			CURLOPT_POST           => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
			CURLOPT_URL            => $package->domain . $package->callback,
			CURLOPT_POSTFIELDS     => $package->payload
		) );
		$response = curl_exec( $curl );
		$code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		// if error occured, get description
		if ( curl_errno( $curl ) ) {
			$ret = array(
				"code" => "-1",
				"description" => curl_error( $curl ),
			);
		} else {
			// forwards response
			$ret = array(
				"code"     => $code,
				"response" => json_decode( $response ),
			);

		}
		curl_close( $curl );
		return (object) $ret;
	}

	function check_domain_payload() {
		global $ERR;
	}

	function authenticate() {
		global $GW, $DB, $ERR;

		if ( empty( $_POST ) ) exit;
		// save a reference to the payload
		$this->payload = @json_decode( $_POST["payload"] );

		switch( $_POST["action"] ) {
			// DEVICE authentications
			case "DEVICE:register":
				return $_POST;
			case "DEVICE:register-ack":
				// get details
				$idD = $GW->id_details( $this->payload->ticket );
				// check if ticket exists
				$row = $DB->get_row( sprintf( "SELECT * FROM wl_ticket WHERE ID='%s';", $idD->db_id ) );
				if ( empty( $row ) ) {
					$ERR->code( 513 ); // ticket does not exist
				}
				// check if ticket still is valid
				if ( $row->ends < date( 'Y-m-d H:i:m' ) ) {
					$ERR->code( 512 ); // ticket to old
				}
				// check authentication code
				$this->initial_msg = json_decode( $row->message );
				if ( $this->payload->authentication != sha1( $this->initial_msg->ID . $this->initial_msg->secret ) ) {
					$ERR->code( 510 ); // bad authentication code 
				}
				return $_POST;
			case "DEVICE:renew-secret":
				// get device
				$row = $DB->get_row( sprintf( "SELECT * FROM wl_device WHERE ID='%s';", $this->payload->device ) );
				if ( empty( $row ) ) {
					$ERR->code( 514 ); // device does not exist
				}
				// remeber device details
				$this->payload->device = (object) $row;
				// check authentication code
				if ( $this->payload->authentication != sha1( $this->payload->ticket . $row->secret ) ) {
					$ERR->code( 510 ); // bad authentication code 
				}
				return $_POST;
			case "DEVICE:qr-code":
				// get device
				$row = $DB->get_row( sprintf( "SELECT * FROM wl_device WHERE ID='%s';", $this->payload->device ) );
				if ( empty( $row ) ) {
					$ERR->code( 514 ); // device does not exist
				}
				// remeber device details
				$this->payload->device = (object) $row;
				// check device is active
				if ( ! $row->is_active ) {
					$ERR->code( 515 );
				}
				// check authentication code 
				if ( $this->payload->authentication != sha1( $this->payload->timestamp . $this->payload->qr . $row->secret ) ) {
					$ERR->code( 510 );
				}
				// get code details
				$detail = $GW->id_details( $this->payload->qr );
				// get the ticket
				$ticket = $DB->get_row( sprintf( "SELECT * FROM wl_ticket WHERE ID='%s';", $detail->db_id ) );
				if ( empty( $ticket ) ) {
					// ticket does not exist
					$ERR->code( 513 );
				}
				// decode message json
				$ticket->message = json_decode( $ticket->message );
				// check tickets time code
				if ( $ticket->ends < date( 'Y-m-d H:i:s' ) ) {
					// ticket is to old
					$ERR->code( 512 );
				}
				$this->payload->ticket = $ticket;
				return $_POST;
			case "DEVICE:unregister":
				// get device
				$row = $DB->get_row( sprintf( "SELECT * FROM wl_device WHERE ID='%s';", $this->payload->device ) );
				if ( empty( $row ) ) {
					$ERR->code( 514 ); // device does not exist
				}
				// remeber device details
				$this->payload->device = (object) $row;
				// check device is active
				if ( ! $row->is_active ) {
					$ERR->code( 515 );
				}
				// check authentication code 
				if ( $this->payload->authentication != sha1( $row->ID . $row->secret ) ) {
					$ERR->code( 510 );
				}
				return $_POST;
			case "DEVICE:remove":
				// get device
				$row = $DB->get_row( sprintf( "SELECT * FROM wl_device WHERE ID='%s';", $this->payload->device ) );
				if ( empty( $row ) ) {
					$ERR->code( 514 ); // device does not exist
				}
				// remeber device details
				$this->payload->device = (object) $row;
				if ( $this->payload->authentication != sha1( $row->ID . $row->secret ) ) {
					$ERR->code( 510 ); // bad authentication code 
				}
				return $_POST;
			/*
			 * DOMAIN authentications
			 */
			case "DOMAIN:register":
				// common domain payload checks
				if ( empty( $this->payload->domain ) )  $ERR->code( 509, "domain" );
				if ( empty( $this->payload->name ) )    $ERR->code( 509, "name" );
				if ( empty( $this->payload->favicon ) ) $ERR->code( 509, "favicon" );
				if ( empty( $this->payload->ticket ) )  $ERR->code( 509, "ticket" );
				// save record data
				$this->domain_record = $this->get_IP_by_domain( $this->payload->domain );
				if ( empty( $this->domain_record->ip ) ) {
					$ERR->code( 516 );
				}
				// check domain in database
				$row = $DB->get_row( sprintf( "SELECT * FROM wl_domain WHERE domain='%s';", $this->payload->domain ) );
				if ( ! empty( $row ) ) {
					$ERR->code( 517 );
				}
				return $_POST;
			case "DOMAIN:renew-secret":
				// common domain payload checks
				if ( empty( $this->payload->ID ) )             $ERR->code( 509, "ID" );
				if ( empty( $this->payload->callback ) )       $ERR->code( 509, "callback" );
				if ( empty( $this->payload->ticket ) )         $ERR->code( 509, "ticket" );
				if ( empty( $this->payload->authentication ) ) $ERR->code( 509, "authentication" );
				// check domain in database
				$row = $DB->get_row( sprintf( "SELECT * FROM wl_domain WHERE ID='%s';", $this->payload->ID ) );
				if ( empty( $row ) ) {
					$ERR->code( 518 );
				}
				// remeber domain details
				$this->payload->server = (object) $row;
				// check authentication code
				if ( $this->payload->authentication != sha1( $this->payload->ticket . $row->secret ) ) {
					$ERR->code( 510 );
				}
				return $_POST;
			case "DOMAIN:qr-code":
				if ( empty( $this->payload->ID ) )             $ERR->code( 509, "ID" );
				if ( empty( $this->payload->callback ) )       $ERR->code( 509, "callback" );
				if ( empty( $this->payload->authentication ) ) $ERR->code( 509, "authentication" );
				// check domain in database
				$row = $DB->get_row( sprintf( "SELECT * FROM wl_domain WHERE ID='%s';", $this->payload->ID ) );
				if ( empty( $row ) ) {
					$ERR->code( 518 );
				}
				// remeber domain details
				$this->payload->server = (object) $row;
				// check authentication code
				if ( $this->payload->authentication != sha1( $row->ID . $row->secret ) ) {
					$ERR->code( 510 );
				}
				return $_POST;
			case "DOMAIN:unregister":
				// check for missing parameters
				if ( empty( $this->payload->ticket ) )         $ERR->code( 509, "ticket" );
				if ( empty( $this->payload->ID ) )             $ERR->code( 509, "ID" );
				if ( empty( $this->payload->callback ) )       $ERR->code( 509, "callback" );
				if ( empty( $this->payload->authentication ) ) $ERR->code( 509, "authentication" );
				// get server
				$row = $DB->get_row( sprintf( "SELECT * FROM wl_domain WHERE ID='%s';", $this->payload->ID ) );
				if ( empty( $row ) ) {
					$ERR->code( 518 );
				}
				// remeber domain details
				$this->payload->server = (object) $row;

				// save record data
				$this->domain_record = $this->get_IP_by_domain( $row->domain );
				if ( empty( $this->domain_record->ip ) ) {
					$ERR->code( 516 );
				}
				// check callback file
				$check = $this->curl_call( (object) array(
					"domain"   => $row->domain,
					"callback" => $this->payload->callback,
					"payload"  => array(
						"action" => "unregister"
					)
				) );
				if ( $check->code != 200 ) {
					$ERR->code( 522 );
				}
				// check authentication code
				if ( $this->payload->authentication != sha1( $row->ID . $row->secret ) ) {
					$ERR->code( 510 );
				}
				return $_POST;
			default:
				$ERR->code( 502 );
		}
	}

}

$PKG = new Package;

?>