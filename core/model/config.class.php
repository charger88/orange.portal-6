<?php

class OPAM_Config extends OPDB_Object {
	
	protected static $table = 'config';
	
	protected static $schema = array(
		'id'             => array(0 ,'ID'),
		'config_status'  => array(1 ,'BOOLEAN'),
		'config_type'    => array('','VARCHAR',8),
		'config_key'     => array('','VARCHAR',64),
		'config_value'   => array('','VARCHAR',512),
	);

	protected static $indexes = array('config_status');
	protected static $uniq = array('config_key');

    public function set($field, $value){
        if ($field === 'config_value'){
            $value = $this->compositeValueToString($value,$this->get('config_type'));
        }
        return parent::set($field, $value);
    }

    public function save(){
        $this->set(
            'config_value',
            $this->compositeValueToString(
                $this->valueOfType($this->get('config_value'),$this->get('config_type'),self::$schema['config_value'][2]),
                $this->get('config_type')
            )
        );
        return parent::save();
    }


    public static function loadActive($module = null){
		$ref = array();
		$select = new OPDB_Select(self::$table);
		$select->addWhere(new OPDB_Clause('config_status','=',1));
		if (!is_null($module)){
			$select->addWhereAnd(new OPDB_Clause('config_key','LIKE',$module.'_%'));
		}
		$select->execQuery();
		while ($row = $select->getNext()){
            $ref[$row['config_key']] = self::valueOfType($row['config_value'],$row['config_type'],self::$schema['config_value'][2]);
		}
		return $ref;
	}

	public static function loadList($module = null){
		$select = new OPDB_Select(self::$table);
		$select->addWhere(new OPDB_Clause('config_key','LIKE',$module.':%'));
		return $select->execQuery()->getResultArray(false,__CLASS__);
	}
	
	
}
