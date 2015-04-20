<?php

include_once( 'log.php' );

class ARCHIVE {

	function __construct() {
		$this->name = 'ARCHIVE';
	}

	function __destruct() {
		
	}

	function log( $type, $str ) {
		global $LOG;
		// forward log string
		$LOG->write( $this->name, $type, $str );
	}

	function file( $old_path ) {
		// file info
		$parts = pathinfo( $old_path );
		// prepare archivation
		$modtime = filemtime( $old_path );

		switch ( $parts["extension"] ) {
			case "log": // log file
				$dir = LOG_DIR . "archive/". date( "Y/m", $modtime );
				$url = $dir;
				$filename = date( "d", $modtime );
				break;
			case "xml": // feed item
				$dir = FEED_DIR . "archive/". date( "Y/m/d", $modtime );
				$url = $dir;
				$filename = $parts["filename"];
				break;
			default: // images & files
				// Get the path to the upload directory.
				$upload_dir = wp_upload_dir();
				$dir = $upload_dir["path"];
				$url = $upload_dir["url"];
				$filename = $parts["filename"];
				break;
		}
		if ( ! file_exists( $dir ) ) {
			// create folder(s)
			mkdir( $dir, 0777, true );
		}
		// move tile to new location
		$new_path = $dir ."/". $filename .".". $parts["extension"];
		$new_url = $url ."/". $filename .".". $parts["extension"];
		// move file
		//rename( $old_path, $new_path );
		// temp dev
		copy( $old_path, $new_path );

		// return new filepath
		return $new_url;
	}

}

$ARC = new ARCHIVE;
