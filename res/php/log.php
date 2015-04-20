<?php

include_once( 'config.php' );
include_once( 'archive.php' );

$LOG = new LOG;

class LOG {

	function __construct() {
		global $ARC;

		// prepare logging
		$this->file_path = LOG_DIR .'today.log';

		// prepare log file rotation
		$modtime = filemtime( $this->file_path );
		$moddate = date( 'y/m/d', $modtime );
		$nowdate = date( 'y/m/d' );

		// rotate log files if required
		if ( $moddate != $nowdate ) {
			// archive log file
			$ARC->file( $this->file_path );
		}
		// prepare log file
		$this->file = fopen( $this->file_path, 'a+' );
	}

	function __destruct() {
		// free up resource
		fclose( $this->file );
	}

	function write( $class, $type, $str ) {
		// log types/tags
		$tag = array(
			'debug' => 'DEBUG',
			'info'  => 'INFO',
			'error' => 'ERROR'
		);
		switch ( LOG_LEVEL ) {
			case 'error':
				// append string to log file
				if ( $type == 'error' ) {
					fwrite( $this->file, "[". $tag[ $type ] ."][". $class ."] ". $str ."\n" );
				}
				break;
			case 'verbose':
				// append string to log file
				fwrite( $this->file, "[". $tag[ $type ] ."][". $class ."] ". $str ."\n" );
				break;
			default:
				if ( LOG_LEVEL == $type) {
					// append string to log file
					fwrite( $this->file, "[". $tag[ $type ] ."][". $class ."] ". $str ."\n" );
				}
		}
	}

}
