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
	
	public static function loadActive($module = null){
		$ref = array();
		$select = new OPDB_Select(self::$table);
		$select->addWhere(new OPDB_Clause('config_status','=',1));
		if (!is_null($module)){
			$select->addWhereAnd(new OPDB_Clause('config_key','LIKE',$module.'_%'));
		}
		$select->execQuery();
		while ($row = $select->getNext()){
			if ($row['config_type'] == 'array'){
				$row['config_value'] = json_decode($row['config_value'],true);
			} else if ($row['config_type'] == 'boolean'){
				$row['config_value'] = boolval($row['config_value']);
			} else if ($row['config_type'] == 'integer'){
				$row['config_value'] = intval($row['config_value']);
			} else if ($row['config_type'] == 'float'){
				$row['config_value'] = floatval($row['config_value']);
			}
			$ref[$row['config_key']] = $row['config_value'];
		}
		return $ref;
	}

	public static function loadList($module = null){
		$select = new OPDB_Select(self::$table);
		$select->addWhere(new OPDB_Clause('config_key','LIKE',$module.':%'));
		return $select->execQuery()->getResultArray(false,__CLASS__);
	}
	
	
}
