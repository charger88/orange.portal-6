<?php

class OPDB_Clause {
	
	private $key;
	private $operation;
	private $value;
	
	// Create clause object
	function __construct($key,$operation,$value,$link = false){
		
		if (is_array($key)) {
			// Key field with function. For example: count(*) will be array('count','*')
			$this->key = OPDB_Functions::stringEscape($key[0]).'('.OPDB_Functions::getRealFieldName($key[1]).')';
		} else {
			$this->key = OPDB_Functions::getRealFieldName($key);
		}
		
		$this->operation = $this->getOperation($operation);	
		
		if ($link) {
			
			if (is_array($value)) {
				// Key field with function. For example: count(*) will be array('count','*')
				$this->value = OPDB_Functions::stringEscape($value[0]).'('.OPDB_Functions::getRealFieldName($value[1]).')';
			} else {
				$this->value = OPDB_Functions::getRealFieldName($value);
			}
			
		} else {
			
			if (($this->operation == 'IN')||($this->operation == 'NOT IN')) {
				
				$values = '';
				
				if ($value instanceof OPDB_Select) {
					// Sub-query. For example: (SELECT item_id ...)
					$values = $value->getSQL();
				} else if (is_array($value)){
					$value = array_unique($value);
					foreach ($value as $i => $val){
						if ($i > 0) {
							$values .= ',';
						}
						$values .= "'".OPDB_Functions::stringEscape($val)."'";
					}
				
				}
			
				$this->value = '('.$values.')';
				
			} else {
				
				if (is_array($value)){
					$this->value = OPDB_Functions::stringEscape($value[0])."('".OPDB_Functions::stringEscape($value[1])."')";
				} else {			
					$this->value = "'".OPDB_Functions::stringEscape($value)."'";
				}
				
			}
			
		}
		
	}
	
	// SQL Code for query
	public function getSQL(){
		return "$this->key $this->operation $this->value";	
	}
		
	private function getOperation($operation){
		
		$operation = strtoupper($operation);
		
		$actions = array('=','>','<','!=','>=','<=','<>','LIKE','NOT LIKE','IN','NOT IN');
		
		if (!in_array($operation,$actions)){
			OPDB_Functions::errorMessage('Bad OPDB clause operation code!');
		}
		
		return $operation;
			
	}
	
	public function getHash(){
		return md5($this->key.';'.$this->operation.';'.$this->value);
	}
		
}