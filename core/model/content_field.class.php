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

    public function set($field, $value){
        if ($field === 'content_field_value'){
            $value = $this->compositeValueToString($value,$this->get('content_field_type'));
        }
        return parent::set($field, $value);
    }

    public function save(){
        $this->set(
            'content_field_value',
            $this->compositeValueToString(
                $this->valueOfType($this->get('content_field_value'),$this->get('content_field_type'),self::$schema['content_field_value'][2]),
                $this->get('content_field_type')
            )
        );
        return parent::save();
    }

    public static function getObject($content_id,$field){
		$select = new OPDB_Select('content_field');
        $select->addWhere(new OPDB_Clause('content_id', '=', $content_id));
        $select->addWhereAnd(new OPDB_Clause('content_field_name', '=', $field));
		$fieldObject = new OPAM_Content_Field($select->execQuery()->getNext());
		if (!$fieldObject->id){
			$fieldObject->set('content_id',$content_id);
			$fieldObject->set('content_field_name',$field);
		}
		return $fieldObject;
	}
	
	public static function getContentIDs($name,$value){
		$select = new OPDB_Select(self::$table);
		$select->addWhere(new OPDB_Clause('content_field_name','=',$name));
		$select->addWhereAnd(new OPDB_Clause('content_field_value','=',$value));
		$select->addField('content_id');
		return $select->execQuery()->getResultArray(true);
	}
	
}
