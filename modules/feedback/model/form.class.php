<?php

class OPMM_Feedback_Form extends \Orange\Database\ActiveRecord
{

	protected static $table = 'feedback_form';

	protected static $scheme = [
		'id' => ['type' => 'ID'],
		'feedback_form_name' => ['type' => 'STRING', 'length' => 256],
		'feedback_form_themes' => ['type' => 'STRING', 'length' => 4096],
		'feedback_form_theme' => ['type' => 'TINYINT'],
		'feedback_form_phone' => ['type' => 'TINYINT'],
		'feedback_form_email' => ['type' => 'TINYINT'],
		'feedback_form_uname' => ['type' => 'TINYINT'],
		'feedback_form_text' => ['type' => 'TINYINT'],
		'feedback_form_fields' => ['type' => 'ARRAY', 'length' => 8192],
		'feedback_form_send_to' => ['type' => 'STRING', 'length' => 256],
	];

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