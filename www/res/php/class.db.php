<?php
/*
 * Class Database
 *
 */

require_once __DIR__ .'/config.php';
require_once __DIR__ .'/class.error.php';
require_once __DIR__ .'/class.log.php';

class Database {

	function __construct( ) {
		$this->name = 'DB';
		$this->conn = new PDO(
			"mysql:host=". DB_HOST .";dbname=". DB_NAME, DB_USER, DB_PASSWORD,
			array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" )
		);
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	function __destruct( ) {
		$this->conn = null;
	}

	private function log( $type, $str ) {
		global $LOG;

		$LOG->write( $this->name, $type, $str );
	}

	function get_results( $query, $data = null ) {
		$handle = $this->query( $query, $data );
		$ret = $handle->fetchAll( PDO::FETCH_OBJ );
		return $ret;
	}

	function get_row( $query ) {
		$handle = $this->conn->prepare( $query );
		$handle->execute();
		$rows = $handle->fetchAll( PDO::FETCH_OBJ );
		if ( count( $rows ) ) {
			return $rows[0];
		}
	}

	function get_var( $query ) {
		$handle = $this->conn->prepare( $query );
		$handle->execute();
		$rows = $handle->fetchAll( PDO::FETCH_OBJ );
		if ( count( $rows ) ) {
			foreach ( $rows[0] as $row => $val ) {
				return $val;
			}
		}
	}

	function query( $query, $data = null ) {
		$handle = $this->conn->prepare( $query );
		$handle->execute( $data );
		// remeber last insert id, if INSERTing
		if ( preg_match( "/INSERT/i", $query ) ) {
			$this->insert_id = $this->conn->lastInsertId();
		}
		return $handle;
	}
	
	function batch( $batch_qry ) {
		global $ERR, $RSP;

		foreach ( $batch_qry as $query ) {
			$handle = $this->conn->prepare( $query );
			$handle->execute();
			// remeber last insert id, if INSERTing
			if ( preg_match( "/INSERT/i", $query ) ) {
				$this->insert_id = $this->conn->lastInsertId();
			}
		}
	}
	
}

$DB = new Database;

?>