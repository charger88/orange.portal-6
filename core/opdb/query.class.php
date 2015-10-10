<?php

abstract class OPDB_Query {
	
	protected $table;
	protected $where = '';
	protected $result = null;
	
	public abstract function __construct($tablename);
	public abstract function execQuery();
	
	protected function processQuery($query){
		$this->result = OPDB_Database::execQuery($query);
	}
		
	public function addWhereOr($clause = null){
		$this->where .= ' OR ';
		if (!is_null($clause)){
			$this->addWhere($clause);
		}
	}
		
	public function addWhereAnd($clause = null){
		$this->where .= ' AND ';
		if (!is_null($clause)){
			$this->addWhere($clause);
		}
	}
	
	public function addWhere($clause){
		if ($clause instanceof OPDB_Clause) {
			$this->where .= $clause->getSQL();
		} else {
			//print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
			OPDB_Functions::errorMessage('Incorrect clause object!');
		}
	}
	
	public function addWhereBracket($open){
		
		if ($open) {
			$this->where .= '(';
		} else {
			$this->where .= ')';
		}
				
	}
	
	protected function getWhereSQL(){
		if ($this->where != '') {
			return 'WHERE '.$this->where;
		} else {
			return '';
		}
	}
		
}