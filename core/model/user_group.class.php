<?php

/**
 * Class OPAM_User_Group
 */
class OPAM_User_Group extends \Orange\Database\ActiveRecord
{

	/**
	 * @var string
	 */
	protected static $table = 'user_group';

	/**
	 * @var array
	 */
	protected static $scheme = [
		'id' => ['type' => 'ID'],
		'group_name' => ['type' => 'STRING', 'length' => 32],
		'group_description' => ['type' => 'STRING', 'length' => 256],
		'group_module' => ['type' => 'STRING', 'length' => 32],
	];

	/**
	 * @param bool $translate
	 * @return array
	 */
	public static function getRef($translate = false)
	{
		$ref = (new \Orange\Database\Queries\Select(self::$table))
			->setOrder('group_name')
			->addField('id')
			->addField('group_name')
			->execute()
			->getResultColumn('id', 'group_name');
		$ref[0] = 'USER_GROUP_EVERYBODY';
		if ($translate) {
			foreach ($ref as $index => $value) {
				$ref[$index] = OPAL_Lang::t($value);
			}
		}
		return $ref;
	}

	/**
	 * @return OPAM_User_Group[]
	 */
	public static function getList()
	{
		return (new \Orange\Database\Queries\Select(self::$table))
			->setOrder('id')
			->execute()
			->getResultArray(null, __CLASS__);
	}

}