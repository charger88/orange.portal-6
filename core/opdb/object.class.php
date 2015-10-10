<?php

abstract class OPDB_Object {
	
	public $id;
	protected $data;

	static protected $table = array();
	static protected $schema = array();
	static protected $indexes = array();
	static protected $uniq = array();


	public function __construct($field = null,$value = null){
		$this->load($field,$value);
	}
		
	public function getStructure(){
		return array(static::$table,static::$schema,static::$indexes,static::$uniq);
	}
	
	public function getTable(){
		return static::$table;
	}
	
	public function getDataArray(){
		return $this->data;
	}
	
	public function delete($null = false){
		if ($this->id) {
			$delete = new OPDB_Delete(static::$table);
			$delete->addWhere(new OPDB_Clause('id','=',$this->id));
			$result = $delete->execQuery();
			$keys = $this->getMCacheKeys();
			foreach($keys as $key){
				OPDB_MCache::delete($key);
			}
			if ($result > 0){
				if ($result > 1){
					OPDB_Functions::errorMessage('Error! Deleted more than one record!');
					return false;
				} else {
					if ($null){
						$this->id = null;
						$this->data = null;
					}
					return true;
				}
			} else {
				return false;
			}
		} else {
			return null;
		}
	}

    /**
     * @return int|null
     */
    public function save(){
		if ($this->id) {
			$save = new OPDB_Update(static::$table);
			$save->addWhere(new OPDB_Clause('id','=',$this->id));
		} else {
			$save = new OPDB_Insert(static::$table);
		}
		foreach ($this->data as $field => $value){
			if ($field != 'id') {
				if (static::$schema[$field][1] == 'ARRAY'){
					$value = !is_array($value) ? $value : ($value ? json_encode($value) : '');
				} else if (static::$schema[$field][1] == 'LIST'){
					$value = !is_array($value) ? $value : ($value ? '|'.implode('|',$value).'|' : '');
				}
				$save->addField($field,$value);
			}
		}
		$result = $save->execQuery();
		if (!$this->id){
			$this->set('id',$result);
		}
		$keys = $this->getMCacheKeys();
		foreach($keys as $key){
			OPDB_MCache::delete($key);
		}		
		return $this->id;
	}
	
	public function load($field,$value){
		$result = null;
		if (is_array($field) && is_null($value)){
			$result = $field;
		} else {
			if (is_null($field)) {
				if (is_array($value) ){
					$result = $value;
				}
			} else {
				if (is_null($value)){
					$value = $field;
					$field = 'id';
				}
				if (($field == 'id') || (in_array($field, static::$uniq))){
					if (false === ($result = OPDB_MCache::get(static::$table.':'.$field.':'.md5($value)))){
						$load = new OPDB_Select(static::$table);
						$load->addWhere(new OPDB_Clause($field,'=',$value));
						$load->setLimit(1);
						$load->execQuery();
						if ($load->getNumRows() == 1){
							$result = $load->getNext();
							OPDB_MCache::set(static::$table.':'.$field.':'.md5($value) , $result , OPDB_Config::$MCTime2);
						} else {
							$result = null;
						}
					}
				}
			}
		}
		if (!is_null($result)){
			$this->setObjectFromArray($result);
		} else {
			foreach (static::$schema as $field => $type) {
				$this->data[$field] = $type[0];
			}
		}
		return $this;
	}
	
	public function setObjectFromArray($data){
		if (is_array($data)) {
			foreach ($data as $field => $value){
				$this->set($field,$value);
			}
		}
		return $this;
	}
	
	public function set($field,$value){		
		if ($this->testField($field)){
			if ($field == 'id'){
				$this->id = intval($value);
			}
			$type = $this->type($field);
			if (!is_null($type)) {
				$this->data[$field] = $this->valueOfType($value, $type[1], (isset($type[2]) ? $type[2] : null) );
				return $this->data[$field];
			} else {
				return false;
			}
			
		} else {
			OPDB_Functions::errorMessage('Error! Undeclarated property '.htmlspecialchars($field,null,'UTF-8').' (set value)!');
			return null;
		}
	}
	
	public function get($field){
		if ($this->testField($field)){
			return $this->data[$field];
		} else {
			print_r(debug_backtrace());
			OPDB_Functions::errorMessage('Error! Undeclarated property '.htmlspecialchars($field,null,'UTF-8').' (get value)!');
			return null;
		}
	}

	public function type($field){
		if ($this->testField($field)){
			return static::$schema[$field];
		} else {
			OPDB_Functions::errorMessage('Error! Undeclarated property '.htmlspecialchars($field,null,'UTF-8').' (get type)!');
			return null;
		}
	}
	
	protected function valueOfType($value,$typeName,$typeSize = null) {
		if (in_array($typeName,array('ID','INTEGER','TINYINT','SMALLINT','MEDIUMINT','INT'))){
			$value = intval(str_replace(' ', '', $value));
		} else if ($typeName == 'BIGINT'){
			$value = str_replace(' ', '', $value);
			$value = (is_numeric($value)) ? $value : null;
		} else if (($typeName == 'FLOAT') || ($typeName == 'DOUBLE')){
			$value = str_replace(',', '.', $value);
			$value = str_replace(' ', '', $value);
			$value = (is_numeric($value)) ? $value : null;
		} else if (($typeName == 'BOOL')||($typeName == 'BOOLEAN')){
			$value = ($value) ? 1 : 0;
		} else if (($typeName == 'CHAR')||($typeName == 'VARCHAR')){
			$value = (strlen($value) > $typeSize) ? substr(''.$value, 0, $typeSize) : ''.$value;
		} else if (($typeName == 'TIMESTAMP')||($typeName == 'DATETIME')){
			$value = date('Y-m-d H:i:s', ( is_numeric($value) ? $value : strtotime($value) ) );
		} else if ($typeName == 'DATE'){
			$value = date('Y-m-d', ( is_numeric($value) ? $value : strtotime($value) ) );
		} else if ($typeName == 'TIME'){
			$value = date('H:i:s', ( is_numeric($value) ? $value : strtotime($value) ) );
		} else if (($typeName == 'TEXT') || ($typeName == 'LONGTEXT')){
			$value = ''.$value;
		} else if ($typeName == 'ARRAY'){
			$value = is_null($value) ? array() : (is_array($value) ? $value : ($value ? json_decode($value,true) : array()));
		} else if ($typeName == 'LIST'){
			$value = is_array($value) ? $value : (strlen($value = trim($value,'|')) ? explode('|',$value) : array());
			asort($value);
		}
		return $value;
	}
	
	public function testField($field){
		return array_key_exists($field,static::$schema);
	}
	
	public function setFromArray($data,$fields = null){
		if (!empty($data)){
			$fields = is_null($fields) ? array_keys($data) : $fields;
			foreach ($fields as $field) {
				if ($this->testField($field)){
					$this->set($field,isset($data[$field]) ? $data[$field] : static::$schema[$field][0] );
				}
			}
		}
		return $this;
	}	
	
	protected function getMCacheKeys(){
		$fields = array('id');
		if (static::$uniq){
			$fields = array_merge($fields,static::$uniq);
		}
		$keys = array();
		foreach ($fields as $field) {
			if (is_array($field)){
				$values = array();
				foreach ($field as $field_name){
					$values[] = md5($this->get($field_name));
				}
				$values = implode(';', $values);
				$field_text = implode(';', $field);
			} else {
				$values = md5($this->get($field));
				$field_text = $field;
			}
			$keys[] = static::$table.':'.$field_text.':'.$values;
		}
		return $keys;
	}
	
	public static function loadByIDs($IDs,$refField = null){
		if ($IDs && is_array($IDs)){
			$select = new OPDB_Select(static::$table);
			$select->addWhere(new OPDB_Clause('id', 'IN', array_unique($IDs)));
			if (!is_null($refField)){
				$select->addField('id');
				$select->addField($refField);
			}
			return $select->execQuery()->getResultArray(!is_null($refField));
		} else {
			return array();
		}
	}
	
}