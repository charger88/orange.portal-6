<?php

use Orange\Database\Queries\Parts\Condition;

class OPMM_Feedback_Message extends \Orange\Database\ActiveRecord
{

	protected static $table = 'feedback_message';

	protected static $scheme = [
		'id' => ['type' => 'ID'],
		'feedback_message_status' => ['type' => 'TINYINT', 'default' => 0],
		'feedback_message_subject' => ['type' => 'STRING', 'length' => 256],
		'feedback_message_text' => ['type' => 'TEXT'],
		'feedback_message_form_id' => ['type' => 'BIGINT', 'default' => 0],
		'feedback_message_fields' => ['type' => 'ARRAY', 'length' => 4096],
		'feedback_message_time' => ['type' => 'TIME'],
		'feedback_message_sender_user_id' => ['type' => 'BIGINT'],
		'feedback_message_sender_name' => ['type' => 'STRING', 'length' => 64],
		'feedback_message_sender_email' => ['type' => 'STRING', 'length' => 128],
		'feedback_message_sender_phone' => ['type' => 'STRING', 'length' => 32],
		'feedback_message_sender_ip' => ['type' => 'STRING', 'length' => 15],
		'feedback_message_sender_session' => ['type' => 'STRING', 'length' => 32],
		'feedback_message_reply_user_id' => ['type' => 'BIGINT', 'default' => 0],
		'feedback_message_reply_text' => ['type' => 'TEXT', 'default' => ''],
		'feedback_message_reply_from_name' => ['type' => 'STRING', 'default' => '', 'length' => 64],
		'feedback_message_reply_from_email' => ['type' => 'STRING', 'default' => '', 'length' => 64],
		'feedback_message_reply_time' => ['type' => 'TIME'],
	];

	protected static $keys = ['feedback_message_status', 'feedback_message_form_id', 'feedback_message_time'];

	const STATUS_NEW = 0;
	const STATUS_READ = 1;
	const STATUS_REPLIED = 2;
	const STATUS_DELETED = 3;

	public static function getList($statuses = null, $offset = 0, $limit = 25)
	{
		$select = new \Orange\Database\Queries\Select(static::$table);
		if (!is_null($statuses)) {
			$select->addWhere(new Condition('feedback_message_status', 'IN', $statuses));
		}
		return $select->setOrder('feedback_message_time', \Orange\Database\Queries\Select::SORT_DESC)
			->setLimit($limit)
			->setOffset($offset * $limit)
			->execute()
			->getResultArray('id', __CLASS__);
	}

}