<?php

class OPMM_Feedback_Form extends \Orange\Database\ActiveRecord
{

	protected static $table = 'feedback_form';

	protected static $scheme = array(
		'id' => array('type' => 'ID'),
		'feedback_form_name' => array('type' => 'STRING', 'length' => 256),
		'feedback_form_themes' => array('type' => 'STRING', 'length' => 4096),
		'feedback_form_theme' => array('type' => 'TINYINT'),
		'feedback_form_phone' => array('type' => 'TINYINT'),
		'feedback_form_email' => array('type' => 'TINYINT'),
		'feedback_form_uname' => array('type' => 'TINYINT'),
		'feedback_form_text' => array('type' => 'TINYINT'),
		'feedback_form_fields' => array('type' => 'ARRAY', 'length' => 8192),
		'feedback_form_send_to' => array('type' => 'STRING', 'length' => 256),
	);

	const FIELD_STATUS_DISABLED = 0;
	const FIELD_STATUS_ENABLED = 1;
	const FIELD_STATUS_REQUIRED = 2;

	public static function getList()
	{
		return (new \Orange\Database\Queries\Select(static::$table))
			->execute()
			->getResultArray('id', __CLASS__);
	}

	public static function getNextNumber()
	{
		return 1 + (new \Orange\Database\Queries\Select(static::$table))
			->addField(['count', '*'])
			->execute()
			->getResultValue();
	}

}