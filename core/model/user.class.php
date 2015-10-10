<?php

/**
 * Class OPAM_User
 */
class OPAM_User extends OPDB_Object {

    const GROUP_EVERYBODY = 0;
    const GROUP_ADMIN     = 1;
    const GROUP_MANAGER   = 2;
    const GROUP_USER      = 3;

    /**
     * @var string
     */
    protected static $table = 'user';

    /**
     * @var array
     */
    protected static $schema = array(
		'id'               => array(0 ,'ID'),
		'user_login'       => array('','VARCHAR',256),
		'user_email'       => array('','VARCHAR',256),
		'user_pwdhash'     => array('','CHAR',32),
		'user_status'      => array(0 ,'BOOLEAN'),
		'user_groups'      => array(array(),'LIST', 256),
		'user_provider'    => array(0 ,'SMALLINT'),
		'user_phone'       => array('','VARCHAR',32),
		'user_name'        => array('','VARCHAR',256),
		'user_key'         => array('','CHAR',32),
	);

    /**
     * @var array
     */
    protected static $uniq = array('user_login','user_email');

    /**
     * @return int|null
     */
    public function save(){
		if (empty($this->get('user_login'))){
			$this->set('user_login',$this->get('user_email'));
		}
		if (!$this->id){
			$this->set('user_key',md5(rand().$this->get('user_login').time()));
		}
		return parent::save();
	}

    /**
     * @param $password
     * @return string
     */
    public static function makePasswordHash($password){
		return md5($password);
	}

    /**
     * @param array $params
     * @return OPAM_User[]
     */
    public static function getList($params = array()){
		$skip           = isset($params['offset'])          ? abs(intval($params['offset'])) : 0;
        $limit          = isset($params['limit'])           ? abs(intval($params['limit']))  : null;
        $filter         = !empty($params['filter'])         ? $params['filter']              : null;
        $filter_login   = !empty($params['filter_login'])   ? $params['filter_login']        : null;
        $filter_email   = !empty($params['filter_email'])   ? $params['filter_email']        : null;
        $filter_name    = !empty($params['filter_name'])    ? $params['filter_name']         : null;
        $filter_phone   = !empty($params['filter_phone'])   ? $params['filter_phone']        : null;
        $filter_group   = !empty($params['filter_group'])   ? $params['filter_group']        : null;
        $filter_status  = !empty($params['filter_status'])  ? $params['filter_status']       : null;
        $order          = isset($params['order'])           ? $params['order']               : 'id';
        $desc           = isset($params['desc'])            ? $params['desc']                : false;
		$select = new OPDB_Select(self::$table);
        if (!is_null($filter)){
			$select->addWhereBracket(true);
			$select->addWhere(new OPDB_Clause('user_login', 'LIKE', '%'.$filter.'%'));
			$select->addWhereOr(new OPDB_Clause('user_email', 'LIKE', '%'.$filter.'%'));
            $select->addWhereOr(new OPDB_Clause('user_name' , 'LIKE', '%'.$filter.'%'));
            $select->addWhereOr(new OPDB_Clause('user_phone' , 'LIKE', '%'.$filter.'%'));
			$select->addWhereBracket(false);
            $select->addWhereAnd();
        }
        $select->addWhereBracket(true);
            $select->addWhere(new OPDB_Clause('id', '>', 0));
            if (!is_null($filter_login)){
                $select->addWhereAnd(new OPDB_Clause('user_login', 'LIKE', '%'.$filter_login.'%'));
            }
            if (!is_null($filter_email)){
                $select->addWhereAnd(new OPDB_Clause('user_email', 'LIKE', '%'.$filter_email.'%'));
            }
            if (!is_null($filter_name)){
                $select->addWhereAnd(new OPDB_Clause('user_name', 'LIKE', '%'.$filter_name.'%'));
            }
            if (!is_null($filter_phone)){
                $select->addWhereAnd(new OPDB_Clause('user_phone', 'LIKE', '%'.$filter_phone.'%'));
            }
            if (!is_null($filter_group)){
                $select->addWhereAnd(new OPDB_Clause('user_groups', 'LIKE', '%|'.$filter_group.'|%'));
            }
            if (!is_null($filter_status)){
                $select->addWhereAnd(new OPDB_Clause('user_status', '=', $filter_status > 0 ? 1 : 0));
            }
        $select->addWhereBracket(false);
		if (!is_null($limit)){
			$select->setLimit($limit,$limit * $skip);
		}
		$select->setOrder($order,$desc);
		return $select->execQuery()->getResultArray(false,__CLASS__);
	}
	
}