<?php

class OPDB_Functions {
	
	// Returns date in SQL format
	public static function getDate($date = null){
		if (is_null($date)){
			return date('Y-m-d');
		} else {
			return date('Y-m-d',$date);
		}
	}
	
	// Returns time in SQL format
	public static function getTime($time = null){
		if (is_null($time)){
			return date('Y-m-d H:i:s');
		} else {
			return date('Y-m-d H:i:s',$time);
		}
	}
	
	// Truncate table
	public static function truncateTable($tablename){
		return OPDB_Database::execQuery('TRUNCATE TABLE '.self::getRealTableName($tablename).';');
	}
	
	// Returns table name with prefix
	public static function getRealTableName($tablename) {
		
		$tablename = self::stringEscape($tablename);
		
		if (in_array($tablename,OPDB_Config::$CommonTables)) {
			return OPDB_Config::$CommonPrefix.$tablename;
		} else {
			return OPDB_Config::$TablesPrefix.$tablename;
		}
		
	}
	
	// Charset
	public static function setCharset(){
		OPDB_Database::execQuery("SET NAMES 'utf8';");
	}
	
    /**
     * Exception handler
     * @param Exception $ex
     */
    public static function showExeptionMessage($ex){
		//TODO Create correct Exception class
		self::errorMessage('Orange Portal DataBase Core error in '.$ex->getFile().' on line '.$ex->getLine().': "'.$ex->getMessage().'".');
	}
	
	// Error message function
	public static function errorMessage($string){
		echo($string);
	}
	
	// Log query
	public static function log($query,$errors,$time){
		$color = $errors ? 'ff3333' : '333333';
		$logtext  = '<dl style="color: #'.$color.'; margin-bottom: 1em;">';
		$logtext .= '<dt>Query</dt><dd>'.htmlspecialchars($query,ENT_COMPAT,'UTF-8').'</dd>';
		if ($errors){
			$logtext .= '<dt>Error</dt><dd>'.htmlspecialchars($errors,ENT_COMPAT,'UTF-8').'</dd>';
		}
		$logtext .= '<dt>Executing time</dt><dd>'.(round($time*10000)/10000).'</dd>';	
		$logtext .= '</dl>';
		$file = fopen("log.html","a");
		fwrite($file,$logtext);
		fclose($file);
	}
	
	public static function stringEscape($string,$real = true){
		return OPDB_Database::stringEscape($string,$real);
	}
	
	public static function getRealFieldName($field){
		
		$field = self::stringEscape($field);
		
		$dotpos = strpos($field,'.');
		
		if ($dotpos > 0) {
			$tablename = substr($field,0,$dotpos);
			$fieldvalue = substr($field,$dotpos+1);
			$field = self::getRealTableName($tablename).'.'.$fieldvalue;
		}
		
		return $field;
		
	}
	
	public static function getRealFieldValue($value){
		return is_null($value) ? 'null' : "'".self::stringEscape($value)."'";
	}
	
	public static function getReference($tablename,$value_name){
		
		$reference = new OPDB_Select($tablename);
		$reference->addField('id');
		$reference->addField($value_name);
		$reference->execQuery();
		return $reference->getResultArray(true);
		
	}
	
	public static function isTableExists($tablename){
		$result = OPDB_Database::execQuery('SHOW TABLES LIKE \''.self::getRealTableName($tablename).'\';');
		return OPDB_Database::getNumRows($result) == 1;
	}
	
}