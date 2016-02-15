<?php

use \Orange\Database\Queries\Parts\Condition;

class OPAM_Config extends \Orange\Database\ActiveRecord {
	
	protected static $table = 'config';
	
	protected static $scheme = array(
		'id'             => array('type' => 'ID'),
		'config_status'  => array('type' => 'BOOLEAN', 'default' => true),
		'config_type'    => array('type' => 'STRING', 'length' => 8),
		'config_key'     => array('type' => 'STRING', 'length' => 64),
		'config_value'   => array('type' => 'STRING', 'length' => 512),
	);

	protected static $indexes = array('config_status');
	protected static $uniq = array('config_key');

    public function set($field, $value){
        if ($field === 'config_value'){
            $value = $this->compositeValueToString($value,$this->get('config_type'));
        }
        return parent::set($field, $value);
    }

    public static function loadActive($module = null){
		$ref = array();
		$select = new \Orange\Database\Queries\Select(self::$table);
		$select->addWhere(new Condition('config_status','=',1));
		if (!is_null($module)){
			$select->addWhere(new Condition('config_key','LIKE',$module.'_%'));
		}
		$select->execute();
		while ($row = $select->getNext()){
            $ref[$row['config_key']] = unserialize($row['config_value']);
		}
		return $ref;
	}

	public static function loadList($module = null){
        return (new \Orange\Database\Queries\Select(self::$table))
            ->addWhere(new Condition('config_key','LIKE',$module.':%'))
            ->execute()
            ->getResultArray(null,__CLASS__)
        ;
	}
	
	
}
