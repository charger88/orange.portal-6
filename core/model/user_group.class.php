<?php

/**
 * Class OPAM_User_Group
 */
class OPAM_User_Group extends OPDB_Object {

    /**
     * @var string
     */
    protected static $table = 'user_group';

    /**
     * @var array
     */
    protected static $schema = array(
		'id'                 => array(0 ,'ID'),
		'group_name'         => array('','VARCHAR',32),
		'group_description'  => array('','VARCHAR',256),
		'group_module'       => array('','VARCHAR',32),
	);

    /**
     * @param bool $translate
     * @return array
     */
    public static function getRef($translate = false){
		$select = new OPDB_Select(self::$table);
		$select->setOrder('group_name');
		$select->addField('id');
		$select->addField('group_name');
        $ref = $select->execQuery()->getResultArray(true);
        $ref[0] = 'USER_GROUP_EVERYBODY';
        if ($translate){
            foreach ($ref as $index => $value) {
                $ref[$index] = OPAL_Lang::t($value);
            }
        }
		return $ref;
	}

    /**
     * @return OPAM_User_Group[]
     */
    public static function getList(){
        $select = new OPDB_Select(self::$table);
        $select->setOrder('id');
        return $select->execQuery()->getResultArray(false,__CLASS__);
    }
	
}