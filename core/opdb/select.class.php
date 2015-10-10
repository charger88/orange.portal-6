<?php

class OPDB_Select extends OPDB_Query {

	private $fields = '*';
	private $having = '';
	private $limit = 0;
	private $offset = 0;
	private $order = '';	
	private $groupby = '';

	public function __construct($tablename,$mode = null){
		if (is_array($tablename)){
			$tables = $tablename;
			foreach ($tables as $i => $tablename){
				if ($i == 0) {
					$this->table = OPDB_Functions::getRealTableName($tablename);
				} else {
					if (is_null($mode) || !isset($mode[$i-1]) || !isset($mode[$i-1][0])){
						$this->table .= ' ,'.OPDB_Functions::getRealTableName($tablename);
					} else {
						$joinTypes = array('INNER','OUTER','LEFT OUTER','RIGHT OUTER','FULL OUTER','CROSS');
						$joinType = in_array($mode[$i-1][0],$joinTypes) ? $mode[$i-1][0] : 'CROSS';
						$this->table .= ' '.$joinType.' JOIN '.OPDB_Functions::getRealTableName($tablename);
                        if (isset($mode[$i-1][1])){
                            /** @var OPDB_Clause $onClause */
                            $onClause = $mode[$i-1][1];
                            $this->table .= ' ON '.$onClause->getSQL();
                        }
					}
				}
			}
		} else {
			$this->table = OPDB_Functions::getRealTableName($tablename);
		}
	}
	
	public function execQuery(){
		$this->processQuery($this->getSQL().';');
		return $this;
	}
	
	public function getSQL(){
		$where = $this->getWhereSQL();
		$having = $this->getHavingSQL();
		$order = $this->getOrderSQL();
		$limit = $this->getLimitSQL();
		$groupby = $this->getGroupBySQL();
		return ("SELECT $this->fields FROM $this->table $where $groupby $having $order $limit");
	}
	
	public function getNumRows(){
		return OPDB_Database::getNumRows($this->result);
	}
	
	public function getNext($assoc = true){
		return OPDB_Database::getNext($this->result,$assoc);
	}
			
	public function addField($field,$as = null,$distinct = false){
		if ($this->fields == '*'){
			$this->fields = '';
		} else {
			$this->fields .= ',';
		}
		if (is_array($field)) {
			$function = OPDB_Functions::stringEscape($field[0]);	
			$field = OPDB_Functions::getRealFieldName($field[1]);
			$this->fields .= "$function(".($distinct ? 'DISTINCT ' : '')."$field)";
		} else {
			$field = OPDB_Functions::getRealFieldName($field);
			$this->fields .= ($distinct ? 'DISTINCT ' : '').$field;
		}
		if (!is_null($as)){
			$this->fields .= ' as '.OPDB_Functions::stringEscape($as);
		}
	}
					
	public function addHavingOr(){
		$this->having .= ' OR ';
	}
	
	public function addHavingAnd(){
		$this->having .= ' AND ';
	}

    /**
     * @param OPDB_Clause $clause
     */
    public function addHaving($clause){
        if ($clause instanceof OPDB_Clause) {
            $this->having .= $clause->getSQL();
        } else {
            //print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            OPDB_Functions::errorMessage('Incorrect clause object!');
        }
	}
		
	public function addHavingBracket($open){
		if ($open) {
			$this->having .= '(';
		} else {
			$this->having .= ')';
		}		
	}

	public function setLimit($limit,$offset = 0){
		$this->limit = intval($limit);
		$this->offset = intval($offset);
	}
			
	public function setOrder($field,$desc = false){	
		$this->order = '';
		$this->addOrder($field, $desc);
	}
	
	public function addOrder($field,$desc = false){	
		
		if ($desc) {
			$desc = 'DESC';	
		} else {
			$desc = 'ASC';
		}
		
		if (!is_null($field)){
			$field = OPDB_Functions::getRealFieldName($field);
			$order = $field.' '.$desc;
		} else {
			$order = 'RAND()';
		}
		
		if ($this->order != '') {
			$this->order .= ', '.$order;
		} else {
			$this->order = $order;
		}
		
	}
	
	public function setGroupBy($field){
		$this->groupby = OPDB_Functions::getRealFieldName($field);	
	}
	
	public function addGroupBy($field){
		if ($this->groupby != ''){
			$this->groupby .= ','.OPDB_Functions::getRealFieldName($field);
		} else {
			$this->setGroupBy($field);
		}	
	}

    /**
     * @param bool $ref
     * @param string|null $classname
     * @param array $outerData
     * @return OPDB_Object[]|array
     */
    public function getResultArray($ref = false,$classname = null,&$outerData = array()){
		$result = array();
		$rNum = $this->getNumRows();
		for ($i = 0; $i < $rNum; $i++){
			if ($ref){
				$resultTMP = $this->getNext(false);
				if (isset($resultTMP[1])){
					$result[$resultTMP[0]] = $resultTMP[1];
				} else {
					$result[] = $resultTMP[0];
				}
			} else {
				$resArray = $this->getNext();
				if ($outerData){
					$outerDataColumns = array_keys($outerData);
					foreach ($outerDataColumns as $odColumn){
						$outerData[$odColumn][] = $resArray[$odColumn];
					}
				}
				if (isset($resArray['id'])){
					$result[$resArray['id']] = $classname ? new $classname($resArray) : $resArray;
				} else {
					$result[] = $classname ? new $classname($resArray) : $resArray;
				}
			}
		}
		return $result;
	}
	
	// Method to get functions result
	public function getResult(){
		if ($this->getNumRows() == 1) {
			$result = $this->getNext(false);
			return $result[0];
		} else {
			return null;
		}		
	}
	
	public function getTotalRecords(){
		return OPDB_Database::getLastSelectTotalRecords();
	}
		
	private function getHavingSQL(){
		if ($this->having != '') {
			return 'HAVING '.$this->having;
		} else {
			return '';
		}
	}
	
	private function getGroupBySQL(){
		if ($this->groupby != '') {
			return 'GROUP BY '.$this->groupby;
		} else {
			return '';
		}
	}
	
	private function getLimitSQL(){
		if ($this->limit > 0) {
			return 'LIMIT '.$this->limit.' OFFSET '.$this->offset;
		} else {
			return '';
		}
	}
	
	private function getOrderSQL(){
		if ($this->order != '') {
			return 'ORDER BY '.$this->order;
		} else {
			return '';
		}
	}
	
}