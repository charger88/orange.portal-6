<?php

class OPDB_Database {
	
	public static $connect = null;
	
	// DB connector
	public static function init(){
		
		try {

			self::connectToServer();
			
			if (!self::$connect) {
				throw new Exception('There is no connection to database server',1);
			}
			
		} catch (Exception $ex){
			OPDB_Functions::showExeptionMessage($ex);
		}
		
	}
	
	public static function connectToServer(){
		self::$connect = @mysqli_connect(
			OPDB_Config::$DBHost,
			OPDB_Config::$DBUser,
			OPDB_Config::$DBPass,
			OPDB_Config::$DBName,
			OPDB_Config::$DBPort
		);
		if (self::$connect){
			OPDB_Functions::setCharset();
		}
	}
	
	// Query execution method
	public static function execQuery($query){

		if (is_null(self::$connect)) {
			self::init();
		}

        $result = null;

		try {
		
			if (!is_null(self::$connect)) {
				if (OPDB_Config::$DBDebugMode) {
					$query_time_before = array_sum(explode(' ', microtime()));
                    $result = mysqli_query(self::$connect,$query);
					$query_time_after = array_sum(explode(' ', microtime()));
					OPDB_Functions::log($query,self::getErrors(),$query_time_after - $query_time_before);
				} else {
                    $result = mysqli_query(self::$connect,$query);
                }
			} else {
				throw new Exception('There is no connection to database',1);
			}
		
		} catch (Exception $ex){
            $result = null;
			OPDB_Functions::showExeptionMessage($ex);
		}
		
		return $result;
		
	}
	
	// Method returns query errors string
	public static function getErrors(){
		$result = mysqli_error(self::$connect);
		return $result;
	}
	
	// Returns number of affected rows
	public static function getAffectedRows(){
		$result = mysqli_affected_rows(self::$connect);
		return $result;
	}
	
	// Return escaped string
	public static function stringEscape($string,$real = true){

		if (is_null(self::$connect)) {
			self::init();
		}
		if ($real) {
			$string = mysqli_real_escape_string(self::$connect,$string);
		} else {
			$string = mysqli_escape_string(self::$connect,$string);
		}
		
		return $string;
	}
	
	// Return number of selected rows
	public static function getNumRows($result){
		if ($result != null) {
			return mysqli_num_rows($result);
		} else {
			return 0;
		}
	}
	
	// Return next row from select result
	public static function getNext($result,$assoc = true){
		if ($assoc){
			if (($result != null)&&($row = mysqli_fetch_assoc($result))) {
				return $row;
			} else {
				return null;
			}
		} else {
			if (($result != null)&&($row = mysqli_fetch_row($result))) {
				return $row;
			} else {
				return null;
			}
		}
		
	}
	
	// Returns ID of latest inserted row
	public static function getLastInsertId(){
		$result = self::getNext(self::execQuery("SELECT LAST_INSERT_ID() as id;"));
		return intval($result['id']);
	}
	
	// Returns total number of records
	public static function getLastSelectTotalRecords(){
		$result = self::getNext(self::execQuery("SELECT FOUND_ROWS() as total_records;"));
		return intval($result['total_records']);
	}
	
}