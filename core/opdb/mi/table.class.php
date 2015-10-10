<?php

class OPDB_Table {
	
	private $table;
	private $schema;
	private $indexes = array();
	private $uniq = array();
	
	public function __construct($structure){
		$this->table = $structure[0];
		$this->schema = $structure[1];
		$this->indexes = $structure[2];
		$this->uniq = $structure[3];
	}
	
	public function createTable(){
		OPDB_Database::execQuery($this->getTableSQL());
		$error = OPDB_Database::getErrors();
		if (empty($error)) {
			$result = true;
			$message = $this->table.': created';
		} else {
			$result = false;
			$message = $this->table.': '.$error;
		}
		return array($result,$message);
	}
	
	public function dropTable(){
		OPDB_Database::execQuery('DROP TABLE `".OPDB_Config::$TablesPrefix.$this->table."`');
		$error = OPDB_Database::getErrors();
		if (empty($error)) {
			$result = true;
			$message = $this->table.': dropped';
		} else {
			$result = false;
			$message = $this->table.': '.$error;
		}
		return array($result,$message);
	}
	
	private function getTableSQL(){
		$sql = "CREATE TABLE `".OPDB_Config::$TablesPrefix.$this->table."` (";
		$start = true;
		foreach ($this->schema as $key => $type){
			if ($start){
				$start = false;
			} else {
				$sql .= ', ';
			}
			$typeName = $type[1];
			if ($typeName == 'ID') {
				$sql .= '`'.$key.'` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ';
			} elseif ($typeName == 'TIMESTAMP') {
				$sql .= '`'.$key.'` TIMESTAMP NOT NULL DEFAULT \'0000-00-00 00:00:00\' ';
			} elseif (($typeName == 'TEXT') || ($typeName == 'LONGTEXT')){
				$sql .= '`'.$key.'` '.$typeName.' NOT NULL ';
			} elseif (($typeName == 'ARRAY') || ($typeName == 'LIST')){
				$sql .= '`'.$key.'` '.( isset($type[2]) ? 'VARCHAR ('.intval($type[2]).')' : 'TEXT' ).' NOT NULL ';
			} else {
				$sql .= '`'.$key.'` '.$typeName.( isset($type[2]) ? '('.intval($type[2]).')' : '' ).' NOT NULL ';
			}
		}	
		$sql .= $this->getIndexesForTable($this->indexes,'INDEX');
		$sql .= $this->getIndexesForTable($this->uniq,'UNIQUE');
		$sql .= ") ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
		return $sql;
	}
	
	private function getIndexesForTable($array,$type){
		$sql = '';
		if ($array) {
			$sql .= ',';
			foreach ($array as $i => $index){
				if (is_array($index)) {
					$sql .= $type.' (';
					foreach ($index as $j => $subindex) {
						$sql .= '`'.$subindex.'`';
						if (!(count($index) == ($j+1))){
							$sql .= ', ';
						}
					}
					$sql .= ')';
				} else {
					$sql .= $type.' (`'.$index.'`)';	
				}
				if (!(count($array) == ($i+1))){
					$sql .= ', ';
				}	
			}
		}
		return $sql;
	}

}