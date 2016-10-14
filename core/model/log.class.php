<?php

use \Orange\Database\Queries\Parts\Condition;

/**
 * Class OPAM_Log
 */
class OPAM_Log extends \Orange\Database\ActiveRecord {

    /**
     * @var string
     */
    protected static $table = 'log';

    /**
     * @var array
     */
    protected static $scheme = array(
		'id'             => array('type' => 'ID'),
		'log_log'        => array('type' => 'STRING', 'length' => 16),
		'log_status'     => array('type' => 'TINYINT'),
		'log_time'       => array('type' => 'TIME'),
		'log_uri'        => array('type' => 'STRING', 'length' => 512),
		'log_ip'         => array('type' => 'STRING', 'length' => 15),
		'log_useragent'  => array('type' => 'STRING', 'length' => 256),
		'log_user_id'    => array('type' => 'INTEGER'),
		'log_classname'  => array('type' => 'STRING', 'length' => 32),
		'log_object_id'  => array('type' => 'INTEGER'),
		'log_message'    => array('type' => 'STRING', 'length' => 512),
		'log_vars'       => array('type' => 'ARRAY'),
	);

    /**
     * @var array
     */
    protected static $keys = array('log_log','log_status');

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
		$email = new OPAL_Email();
        $email->subject = $subject;
        $email->plain_text = $message;
		return $email->send($to);
	}

    /**
     * @param array $params
     * @return OPAM_Log[]
     */
    public static function loadLog($params = array()){

		$log        = isset($params['log'])        ? $params['log'] : null;
		$date_start = isset($params['date_start']) ? date("Y-m-d H:i:s",strtotime($params['date_start'])) : null;
		$min_status = isset($params['min_status']) ? intval($params['min_status']) : null;
		$max_status = isset($params['max_status']) ? intval($params['max_status']) : null;
		$ip         = isset($params['ip'])         ? intval($params['ip']) : null;
		$useragent  = isset($params['useragent'])  ? intval($params['useragent']) : null;
		$user_id    = isset($params['user_id'])    ? intval($params['user_id'])  : null;
		$object     = isset($params['object'])     ? $params['object']           : null;
		$limit      = isset($params['limit'])      ? intval($params['limit']) : null;
		
		$select = new \Orange\Database\Queries\Select(self::$table);

		if (!is_null($log)){
			$select->addWhere(new Condition('log_log', '=', $log));
		}
		
		if (!is_null($min_status)){
			$select->addWhere(new Condition('log_status', '>=', $min_status));
		}
		
		if (!is_null($max_status)){
			$select->addWhere(new Condition('log_status', '<=', $max_status));
		}
		
		if (!is_null($date_start)){
			$select->addWhere(new Condition('log_time', '>', $date_start));
		}
		
		if (!is_null($ip)){
			$select->addWhere(new Condition('log_ip', '=', $ip));
		}
		
		if (!is_null($useragent)){
			$select->addWhere(new Condition('log_useragent', '=', $useragent));
		}
		
		if (!is_null($user_id)){
			$select->addWhere(new Condition('log_user_id', '=', $user_id));
		}
		
		if ($object instanceof \Orange\Database\ActiveRecord){
			$select->addWhere(new Condition('log_classname', 'LIKE', get_class($object)));
			$select->addWhere(new Condition('log_object_id', '=', $object->id));
		}

		$select->setOrder('id',\Orange\Database\Queries\Select::SORT_DESC);
		$select->setLimit($limit);

		return $select->execute()->getResultArray(null,__CLASS__);
	}
	
}