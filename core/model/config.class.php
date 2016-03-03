<?php

use \Orange\Database\Queries\Parts\Condition;

class OPAM_Config extends \Orange\Database\ActiveRecord {
	
	protected static $table = 'config';
	
	protected static $scheme = array(
		'id'             => array('type' => 'ID'),
		'config_status'  => array('type' => 'BOOLEAN', 'default' => true),
		'config_type'    => array('type' => 'STRING', 'length' => 16),
		'config_key'     => array('type' => 'STRING', 'length' => 64),
		'config_value'   => array('type' => 'DATA', 'length' => 512),
	);

	protected static $keys = array('config_status');
	protected static $u_keys = array('config_key');

    public static function loadActive($module = null){
		$ref = array();
		$select = new \Orange\Database\Queries\Select(self::$table);
		$select->addWhere(new Condition('config_status','=',1));
		if (!is_null($module)){
			$select->addWhere(new Condition('config_key','LIKE',$module.'_%'));
		}
		$select->execute();
		while ($row = $select->getResultNextRow()){
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
