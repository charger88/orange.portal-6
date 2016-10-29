<?php

use Orange\Database\Queries\Parts\Condition;

class OPAM_Content_Field extends \Orange\Database\ActiveRecord
{

	protected static $table = 'content_field';

	protected static $scheme = array(
		'id' => array('type' => 'ID'),
		'content_id' => array('type' => 'INTEGER'),
		'content_field_name' => array('type' => 'STRING', 'length' => 32),
		'content_field_type' => array('type' => 'STRING', 'length' => 16),
		'content_field_value' => array('type' => 'DATA', 'length' => 2048),
	);

	protected static $keys = array('content_id');
	protected static $u_keys = array(array('content_id', 'content_field_name'));


	public static function getObject($content_id, $field)
	{
		$select = (new \Orange\Database\Queries\Select(self::$table))
			->addWhere(new Condition('content_id', '=', $content_id))
			->addWhere(new Condition('content_field_name', '=', $field))
			->execute();
		$fieldObject = new OPAM_Content_Field($select->getResultNextRow());
		if (!$fieldObject->id) {
			$fieldObject->set('content_id', $content_id);
			$fieldObject->set('content_field_name', $field);
		}
		return $fieldObject;
	}

	public static function getContentIDs($name, $value)
	{
		return (new \Orange\Database\Queries\Select(self::$table))
			->addWhere(new Condition('content_field_name', '=', $name))
			->addWhere(new Condition('content_field_value', '=', $value))
			->addField('content_id')
			->execute()
			->getResultList('content_id');
	}

	public static function getRef($name)
	{
		return (new \Orange\Database\Queries\Select(self::$table))
			->addWhere(new Condition('content_field_name', '=', $name))
			->addField('content_id')
			->addField('content_field_value')
			->execute()
			->getResultColumn('content_id', 'content_field_value');
	}

}
