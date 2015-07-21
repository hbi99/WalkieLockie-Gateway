<?php

require_once __DIR__ .'/config.php';

/*
 * Class Logger
 * 
 * This class' primary task is to open log-file and
 * keep the file open while executing tasks.
 * 
 * This class keeps track of dates and saves log files
 * in a date-stamp fashion - in order to avoid large log files.
 * 
 */

class LOG {

	function __construct() {
		// prepare logging
		$this->file_path = LOG_DIR .'/today.log';

		// archive file if old
		$this->archive_file( $this->file_path );

		// prepare log file
		$this->file = fopen( $this->file_path, 'a+' );
	}

	function __destruct() {
		// free up resource
		fclose( $this->file );
	}

	/*
	 * This function clears current log.
	 */
	function clear() {
		$file = @fopen( $this->log_path, 'r+' );
		ftruncate( $file, 0 );
		fclose( $file );
	}

	/*
	 * This function archives current log,
	 * if it is created yesterday (or older).
	 */
	private function archive_file( $source ) {
		$mtime    = filemtime( $source );
		$modified = date( 'Ymd', $mtime );
		$now      = date( 'Ymd' );

		// if log file is 'old', archive it
		if ( $now != $modified ) {
			$target = LOG_DIR .'/'. date( 'Y/m', $mtime );
			if ( !is_dir( $target ) ) {
				mkdir( $target, 0777, true );
			}
			$target = $target .'/'. date( 'd', $mtime ) .'.log';
			rename( $source, $target );
		}
	}

	/*
	 * This function logs strings to todays log
	 */
	function write( $class, $type, $str ) {
		// log types/tags
		$tag = array(
			'debug' => 'DEBUG',
			'info'  => 'INFO',
			'error' => 'ERROR'
		);
		switch ( LOG_LEVEL ) {
			case 'error':   // always append error strings to log file
			case 'verbose': // append string to log file
				fwrite( $this->file, "[". $tag[ $type ] ."][". $class ."] ". $str ."\n" );
				break;
			default: // append string to log file
				if ( LOG_LEVEL == $type) {
					fwrite( $this->file, "[". $tag[ $type ] ."][". $class ."] ". $str ."\n" );
				}
				break;
		}
	}

}

$LOG = new LOG;

?>