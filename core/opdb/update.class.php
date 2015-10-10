<?php

class OPDB_Update extends OPDB_Query  {
	
	private $newValues = '';	
	
	public function __construct($tablename){
		$this->table = OPDB_Functions::getRealTableName($tablename);
	}
		
	public function addField($field,$value){
		
		$value = OPDB_Functions::getRealFieldValue($value);
		
		if ($this->newValues == '') {
			$this->newValues = OPDB_Functions::stringEscape($field)."=".$value;
		} else {
			$this->newValues .= ", ".OPDB_Functions::stringEscape($field)."=".$value;
		}
	}
	
	public function execQuery(){
		$this->processQuery('UPDATE '.$this->table.' SET '.$this->newValues.' '.$this->getWhereSQL().';');
		return OPDB_Database::getAffectedRows();
	}
	
}