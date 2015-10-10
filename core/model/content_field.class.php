<?php

class OPAM_Content_Field extends OPDB_Object {
	
	protected static $table = 'content_field';
	
	protected static $schema = array(
		'id'                  => array(0 ,'ID'),
		'content_id'          => array(0 ,'INTEGER'),
		'content_field_name'  => array('','VARCHAR',32),
		'content_field_type'  => array('','VARCHAR',16),
		'content_field_value' => array('','VARCHAR',2048),
	);
	
	protected static $indexes = array('content_id');
	protected static $uniq = array(array('content_id','content_field_name'));
	
	public static function getObject($content_id,$field){
		$select = new OPDB_Select('content_field');
		$select->addWhere(new OPDB_Clause('content_id', '=', $content_id));
		$select->execQuery();
		$fieldObject = new OPAM_Content_Field();
		if (!$fieldObject->id){
			$fieldObject->set('content_id',$content_id);
			$fieldObject->set('content_field_name',$field);
		}
		return $fieldObject;
	}
	
}
