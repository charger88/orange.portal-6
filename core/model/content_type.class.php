<?php

use Orange\Database\Queries\Parts\Condition;

/**
 * Class OPAM_Content_Type
 */
class OPAM_Content_Type extends \Orange\Database\ActiveRecord
{

	/**
	 * @var string
	 */
	protected static $table = 'content_type';

	/**
	 * @var array
	 */
	protected static $scheme = array(
		'id' => array('type' => 'ID'),
		'content_type_name' => array('type' => 'STRING', 'length' => 64),
		'content_type_code' => array('type' => 'STRING', 'length' => 32),
		'content_type_status' => array('type' => 'BOOLEAN'),
		'content_type_type' => array('type' => 'TINYINT'), // 0 - system, 1 - page, 2 - block, 3 - module, 4 - custom
		'content_type_multilang' => array('type' => 'BOOLEAN'),
		'content_type_class' => array('type' => 'STRING', 'length' => 32),
		'content_type_hidden' => array('type' => 'ARRAY', 'length' => 2048),
		'content_type_fields' => array('type' => 'ARRAY'),
		'content_type_texts' => array('type' => 'ARRAY', 'length' => 1024),
		'content_type_sitemap_priority' => array('type' => 'TINYINT'),
	);

	/**
	 * @var array
	 */
	protected static $keys = array('content_type_status');
	/**
	 * @var array
	 */
	protected static $u_keys = array('content_type_code');

	/**
	 * @return string
	 */
	public function getClass()
	{
		if ($this->get('content_type_class')) {
			return $this->get('content_type_class');
		} else {
			if ($this->get('content_type_type') == 1) {
				return 'OPAM_Page';
			} else if ($this->get('content_type_type') == 2) {
				return 'OPAM_Block';
			} else {
				return 'OPAM_Content';
			}
		}
	}

	/**
	 * @param string $output
	 * @return array
	 */
	public static function getPageTypes($output = 'codes')
	{
		return self::getTypes(1, null, $output);
	}

	/**
	 * @param string $output
	 * @return array
	 */
	public static function getBlockTypes($output = 'codes')
	{
		return self::getTypes(2, null, $output);
	}

	/**
	 * @param string $output
	 * @return array
	 */
	public static function getModuleTypes($output = 'codes')
	{
		return self::getTypes(3, null, $output);
	}

	/**
	 * @param string $output
	 * @return array
	 */
	public static function getCustomTypes($output = 'codes')
	{
		return self::getTypes(4, null, $output);
	}

	/**
	 * @return array
	 */
	public static function getTypesForSitemap()
	{
		return (new \Orange\Database\Queries\Select(self::$table))
			->addWhere(new Condition('content_type_status', '=', 1))
			->addWhere(new Condition('content_type_type', 'NOT IN', array(0, 2)))
			->addWhere(new Condition('content_type_code', 'NOT LIKE', 'admin'))
			->addField('content_type_code')
			->addField('content_type_sitemap_priority')
			->execute()
			->getResultColumn('content_type_code', 'content_type_sitemap_priority');
	}

	/**
	 * @return array
	 */
	public static function getList()
	{
		return (new \Orange\Database\Queries\Select(self::$table))
			->setOrder('id')
			->execute()
			->getResultArray(null, __CLASS__);
	}

	protected static $cacheTypesByType = [];

	/**
	 * @param int|array|null $type
	 * @param string|null $type_name
	 * @param string $output
	 * @return array|OPAM_Content_Type[]
	 */
	public static function getTypes($type = null, $type_name = null, $output = 'codes')
	{
		$cacheable = ($output === 'codes') && is_null($type_name);
		if ($type && !is_array($type)) {
			$type = [$type];
		} else if (is_array($type)) {
			sort($type);
		}
		$type_key = $type ? implode(':', $type) : 'ALL';
		if ($cacheable && isset(static::$cacheTypesByType[$type_key])) {
			return static::$cacheTypesByType[$type_key];
		} else {
			$select = new \Orange\Database\Queries\Select(self::$table);
			$select->addWhere(new Condition('content_type_status', '=', 1));
			if (!is_null($type)) {
				$select->addWhere(new Condition('content_type_type', 'IN', $type));
			}
			if (!is_null($type_name) && (in_array(3, $type) || in_array(4, $type))) {
				$select->addWhere(new Condition('content_type_code', '=', $type_name));
			}
			if ($output != '*') {
				$select->addField('content_type_code');
				if ($output == 'ref') {
					$select->addField('content_type_name');
					$res = $select->execute()->getResultColumn('content_type_code', 'content_type_name');
				} else {
					$res = $select->execute()->getResultList('content_type_code');
				}
			} else {
				$res = $select->execute()->getResultArray(null, __CLASS__);
			}
			if ($cacheable) {
				static::$cacheTypesByType[$type_key] = $res;
			}
			return $res;
		}
	}

}