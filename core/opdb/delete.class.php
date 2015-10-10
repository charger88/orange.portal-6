<?php

class OPDB_Delete extends OPDB_Query {
		
	public function __construct($tablename){
		$this->table = OPDB_Functions::getRealTableName($tablename);
	}
	
	public function execQuery(){
		$this->processQuery('DELETE FROM '.$this->table.' '.$this->getWhereSQL().';');
		return OPDB_Database::getAffectedRows();
	}
	
}