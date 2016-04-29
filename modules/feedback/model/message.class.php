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

    public function send($default_to,$form = null){
        if (is_null($form)){
            $form = new OPMM_Feedback_Form($this->get('feedback_message_form_id'));
        }
        $message = [];
        if ($this->get('feedback_message_sender_user_id')) {
            $user = new OPAM_User($this->get('feedback_message_sender_user_id'));
            $message[] = OPAL_Lang::t('feedback_message_sender_user_id') . ': ' . $user->get('user_login') . ' (ID: ' . $user->id . ')';
        }
        if ($this->get('feedback_message_sender_name')) {
            $message[] = OPAL_Lang::t('feedback_message_sender_name') . ': ' . $this->get('feedback_message_sender_name');
        }
        if ($this->get('feedback_message_sender_ip')) {
            $message[] = OPAL_Lang::t('feedback_message_sender_ip') . ': ' . $this->get('feedback_message_sender_ip');
        }
        if ($this->get('feedback_message_sender_email')) {
            $message[] = OPAL_Lang::t('feedback_message_sender_email') . ': ' . $this->get('feedback_message_sender_email');
        }
        if ($this->get('feedback_message_sender_phone')) {
            $message[] = OPAL_Lang::t('feedback_message_sender_phone') . ': ' . $this->get('feedback_message_sender_phone');
        }
        //TODO Fields $message[] = '';
        $message[] = '';
        $message[] = OPAL_Lang::t('feedback_message_text').':';
        $message[] = $this->get('feedback_message_text');
        $email = new OPAL_Email($form->get('feedback_form_name') . ' / ' . $this->get('feedback_message_subject'),implode("\n",$message));
        $email->setReturnPath($this->get('feedback_message_sender_email'));
        $email->send($form->get('feedback_form_send_to') ? $form->get('feedback_form_send_to') : $default_to);
        return $this;
    }

    public function reply(){
        $form = new OPMM_Feedback_Form($this->get('feedback_message_form_id'));
        $email = new OPAL_Email(OPAL_Lang::t('MODULE_FEEDBACK_REPLY_SUBJECT_PREFIX_%s', $this->get('feedback_message_subject')),$this->get('feedback_message_reply_text'));
        $email->setReturnPath($this->get('feedback_message_reply_from_email'));
        $email->send($this->get('feedback_message_sender_email'));
        return $this;
    }

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