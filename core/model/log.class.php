<?php

/**
 * Class OPAM_Log
 */
class OPAM_Log extends OPDB_Object {

    /**
     * @var string
     */
    protected static $table = 'log';

    /**
     * @var array
     */
    protected static $schema = array(
		'id'             => array(0 ,'ID'),
		'log_log'        => array('','VARCHAR',16),
		'log_status'     => array(0 ,'TINYINT'),
		'log_time'       => array('0000-00-00 00:00:00','TIMESTAMP'),
		'log_uri'        => array('','VARCHAR',512),
		'log_ip'         => array('','VARCHAR',15),
		'log_useragent'  => array('','VARCHAR',256  ),
		'log_user_id'    => array(0 ,'BOOLEAN'),
		'log_classname'  => array('','VARCHAR',32),
		'log_object_id'  => array(0 ,'INTEGER'),
		'log_message'    => array('','VARCHAR',512),
		'log_vars'       => array(array(),'ARRAY'),
	);

    /**
     * @var array
     */
    protected static $indexes = array('log_log','log_status');

    /**
     * @param string $to
     * @return bool
     */
    public function send($to){
		$subject = 'Alert from '.OPAL_Portal::config('domain','localhost');
		$message = $this->id ? 'Log: '.OP_WWW.'/admin/log/view/'.$this->id : 'Log was not saved';
		$message .= "\n\n";
		$message .= vsprintf(OPAL_Lang::t($this->get('log_message')), $this->get('log_vars'));
		$message .= "\n";
		$message .= $this->get('log_uri');
		$email = new OPAL_Email($subject,$message);
		return $email->send($to);
	}

    /**
     * @param array $params
     * @return OPAM_Log[]
     */
    public static function loadLog($params = array()){
		
		$log        = isset($params['log'])        ? $params['log'] : null;
		$date_start = isset($params['date_start']) ? OPDB_Functions::getTime(strtotime($params['date_start'])) : null;
		$min_status = isset($params['min_status']) ? intval($params['min_status']) : null;
		$max_status = isset($params['max_status']) ? intval($params['max_status']) : null;
		$ip         = isset($params['ip'])         ? intval($params['ip']) : null;
		$useragent  = isset($params['useragent'])  ? intval($params['useragent']) : null;
		$user_id    = isset($params['user_id'])    ? intval($params['user_id'])  : null;
		$object     = isset($params['object'])     ? $params['object']           : null;
		$limit      = isset($params['limit'])      ? intval($params['limit']) : null;
		
		$select = new OPDB_Select(self::$table);
		$select->addWhere(new OPDB_Clause('id', '>', 0));
		
		if (!is_null($log)){
			$select->addWhereAnd(new OPDB_Clause('log_log', '=', $log));
		}
		
		if (!is_null($min_status)){
			$select->addWhereAnd(new OPDB_Clause('log_status', '>=', $min_status));
		}
		
		if (!is_null($max_status)){
			$select->addWhereAnd(new OPDB_Clause('log_status', '<=', $max_status));
		}
		
		if (!is_null($date_start)){
			$select->addWhereAnd(new OPDB_Clause('log_time', '>', $date_start));
		}
		
		if (!is_null($ip)){
			$select->addWhereAnd(new OPDB_Clause('log_ip', '=', $ip));
		}
		
		if (!is_null($useragent)){
			$select->addWhereAnd(new OPDB_Clause('log_useragent', '=', $useragent));
		}
		
		if (!is_null($user_id)){
			$select->addWhereAnd(new OPDB_Clause('log_user_id', '=', $user_id));
		}
		
		if ($object instanceof OPDB_Object){
			$select->addWhereAnd(new OPDB_Clause('log_classname', 'LIKE', get_class($object)));
			$select->addWhereAnd(new OPDB_Clause('log_object_id', '=', $object->id));
		}

		$select->setOrder('id',true);
		$select->setLimit($limit);
		
		return $select->execQuery()->getResultArray(false,__CLASS__);
	}
	
}