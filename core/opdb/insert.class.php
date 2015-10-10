<?php

class OPDB_Insert extends OPDB_Query  {

    private $fields = '';
    private $values = '';

	public function __construct($tablename){
		$this->table = OPDB_Functions::getRealTableName($tablename);
	}
	
	public function execQuery(){
		$this->processQuery("INSERT INTO $this->table ($this->fields) VALUES ($this->values);");
		return OPDB_Database::getLastInsertId();
	}
		
	public function addField($field,$value){
		
		$value = OPDB_Functions::getRealFieldValue($value);
		
		if ($this->fields == '') {
			$this->fields = OPDB_Functions::stringEscape($field);
			$this->values = $value;
		} else {
			$this->fields .= ", ".OPDB_Functions::stringEscape($field);
			$this->values .= ", ".$value."";
		}
		
	}
	
}