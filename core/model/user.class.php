<?php

use \Orange\Database\Queries\Parts\Condition;

/**
 * Class OPAM_User
 */
class OPAM_User extends \Orange\Database\ActiveRecord {

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
    protected static $scheme = array(
		'id'               => array('type' => 'ID'),
		'user_login'       => array('type' => 'STRING', 'length' => 128),
		'user_email'       => array('type' => 'STRING', 'length' => 128),
		'user_pwdhash'     => array('type' => 'STRING', 'length' => 256),
		'user_status'      => array('type' => 'BOOLEAN'),
		'user_groups'      => array('type' => 'LIST', 'length' => 256),
		'user_provider'    => array('type' => 'SMALLINT'),
		'user_phone'       => array('type' => 'STRING', 'length' => 32),
		'user_name'        => array('type' => 'STRING', 'length' => 256),
		'user_key'         => array('type' => 'CHAR', 'length' => 32),
	);

    /**
     * @var array
     */
    protected static $u_keys = array('user_login','user_email');

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
     */
    public function setPassword($password){
		$this->set('user_pwdhash', password_hash($password, PASSWORD_DEFAULT));
	}

    /**
     * @param $password
     * @return boolean
     */
    public function verifyPassword($password){
        return password_verify($password, $this->get('user_pwdhash'));
    }

    /**
     * @param array $params
     * @return OPAM_User[]
     */
    public static function getList($params = array()){
		$offset         = isset($params['offset'])          ? abs(intval($params['offset'])) : 0;
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
		$select = new \Orange\Database\Queries\Select(self::$table);
        if (!is_null($filter)){
			$select->addWhereBracket(true);
			$select->addWhere(new Condition('user_login', 'LIKE', '%'.$filter.'%'));
			$select->addWhere(new Condition('user_email', 'LIKE', '%'.$filter.'%'), Condition::L_OR);
            $select->addWhere(new Condition('user_name' , 'LIKE', '%'.$filter.'%'), Condition::L_OR);
            $select->addWhere(new Condition('user_phone' , 'LIKE', '%'.$filter.'%'), Condition::L_OR);
			$select->addWhereBracket(false);
            $select->addWhereOperator(Condition::L_AND);
        }
        $select->addWhere(new Condition('id', '>', 0));
        if (!is_null($filter_login)){
            $select->addWhere(new Condition('user_login', 'LIKE', '%'.$filter_login.'%'));
        }
        if (!is_null($filter_email)){
            $select->addWhere(new Condition('user_email', 'LIKE', '%'.$filter_email.'%'));
        }
        if (!is_null($filter_name)){
            $select->addWhere(new Condition('user_name', 'LIKE', '%'.$filter_name.'%'));
        }
        if (!is_null($filter_phone)){
            $select->addWhere(new Condition('user_phone', 'LIKE', '%'.$filter_phone.'%'));
        }
        if (!is_null($filter_group)){
            $select->addWhere(new Condition('user_groups', 'LIKE', '%|'.$filter_group.'|%'));
        }
        if (!is_null($filter_status)){
            $select->addWhere(new Condition('user_status', '=', $filter_status > 0 ? 1 : 0));
        }
		if (!is_null($limit)){
			$select->setLimit($limit);
            $select->setOffset($offset * $limit);
		}
		$select->setOrder($order,$desc ? \Orange\Database\Queries\Select::SORT_DESC : \Orange\Database\Queries\Select::SORT_ASC);
		return $select->execute()->getResultArray(null,__CLASS__);
	}
	
}