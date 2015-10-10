<?php

/**
 * Class OPAM_Privilege
 */
class OPAM_Privilege extends OPDB_Object {

    /**
     * @var string
     */
    protected static $table = 'privilege';

    /**
     * @var array
     */
    protected static $schema = array(
		'id'              => array(0 ,'ID'),
		'privilege_name'  => array('','VARCHAR',64),
		'user_group_id'   => array(0 ,'INTEGER'),
	);

    /**
     * @var array
     */
    protected static $uniq = array(array('privilege_name','user_group_id'));

    /**
     * @param string $name
     * @param OPAM_User $user
     * @return bool
     */
    public static function hasPrivilege($name,$user){
		$user_groups = $user->get('user_groups');
		if (!in_array(OPAM_User::GROUP_ADMIN, $user_groups)){
			$user_groups[] = 0;
			$select = new OPDB_Select(self::$table);
			$select->addWhere(new OPDB_Clause('privilege_name', '=', $name));
			$select->addWhereAnd();
			$select->addWhere(new OPDB_Clause('user_group_id', 'IN', $user_groups));
			$select->addField('id');
			$select->addField('privilege_name');
			return $select->execQuery()->getNumRows() > 0;
		} else {
			return true;
		}
	}

    /**
     * @param array $data
     */
    public static function massPrivilegesDeleting($data){
        if ($data){
            $delete = new OPDB_Delete(self::$table);
            $first = true;
            foreach ($data as $group_id => $privileges){
                if ($privileges){
                    if ($first){
                        $first = false;
                    } else {
                        $delete->addWhereOr();
                    }
                    $delete->addWhereBracket(true);
                    $delete->addWhere(new OPDB_Clause('user_group_id', '=', $group_id));
                    $delete->addWhereAnd(new OPDB_Clause('privilege_name', 'IN', $privileges));
                    $delete->addWhereBracket(false);
                }
            }
            $delete->execQuery();
        }
    }

    /**
     * @param array $data
     */
    public static function massPrivilegesAdding($data){
        if ($data){
            foreach ($data as $group_id => $privileges){
                if ($privileges){
                   foreach ($privileges as $privilege){
                       $item = new OPAM_Privilege();
                       $item->set('privilege_name',$privilege);
                       $item->set('user_group_id',$group_id);
                       $item->save();
                   }
                }
            }
        }
    }

    /**
     * @return array
     */
    public static function getPrivilegesByGroup(){
        $privileges = array();
        $select = new OPDB_Select(self::$table);
        if ($result = $select->execQuery()->getResultArray()){
            foreach ($result as $row){
                if (!isset($privileges[$row['user_group_id']])){
                    $privileges[$row['user_group_id']] = array();
                }
                $privileges[$row['user_group_id']][] = $row['privilege_name'];
            }
        }
        return $privileges;
    }
	
}