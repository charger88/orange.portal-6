<?php

use \Orange\Database\Queries\Parts\Condition;

class OPMM_Feedback_Message extends \Orange\Database\ActiveRecord {
	
	protected static $table = 'feedback_message';
	
	protected static $scheme = array(
		'id'                                => array('type' => 'ID'),
        'feedback_message_status'           => array('type' => 'TINYINT', 'default' => 0),
        'feedback_message_subject'          => array('type' => 'STRING', 'length' => 256),
        'feedback_message_text'             => array('type' => 'TEXT'),
        'feedback_message_form_id'          => array('type' => 'BIGINT', 'default' => 0),
        'feedback_message_fields'           => array('type' => 'ARRAY', 'length' => 4096),
        'feedback_message_time'             => array('type' => 'TIME'),
        'feedback_message_sender_user_id'   => array('type' => 'BIGINT'),
        'feedback_message_sender_name'      => array('type' => 'STRING', 'length' => 64),
        'feedback_message_sender_email'     => array('type' => 'STRING', 'length' => 128),
        'feedback_message_sender_phone'     => array('type' => 'STRING', 'length' => 32),
        'feedback_message_sender_ip'        => array('type' => 'STRING', 'length' => 15),
        'feedback_message_sender_session'   => array('type' => 'STRING', 'length' => 32),
        'feedback_message_reply_user_id'    => array('type' => 'BIGINT', 'default' => 0),
        'feedback_message_reply_text'       => array('type' => 'TEXT', 'default' => ''),
        'feedback_message_reply_from_name'  => array('type' => 'STRING', 'default' => '', 'length' => 64),
        'feedback_message_reply_from_email' => array('type' => 'STRING', 'default' => '', 'length' => 64),
        'feedback_message_reply_time'       => array('type' => 'TIME'),
    );

    protected static $keys = ['feedback_message_status','feedback_message_form_id','feedback_message_time'];

    const STATUS_NEW     = 0;
    const STATUS_READ    = 1;
    const STATUS_REPLIED = 2;
    const STATUS_DELETED = 3;

    public static function getList($statuses = null, $offset = 0, $limit = 25){
        $select = new \Orange\Database\Queries\Select(static::$table);
        if (!is_null($statuses)) {
            $select->addWhere(new Condition('feedback_message_status', 'IN', $statuses));
        }
        return $select->setOrder('feedback_message_time',\Orange\Database\Queries\Select::SORT_DESC)
            ->setLimit($limit)
            ->setOffset($offset * $limit)
            ->execute()
            ->getResultArray('id',__CLASS__)
        ;
    }

}