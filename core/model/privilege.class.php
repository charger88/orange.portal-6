<?php

use Orange\Database\Queries\Parts\Condition;

/**
 * Class OPAM_Privilege
 */
class OPAM_Privilege extends \Orange\Database\ActiveRecord
{

	/**
	 * @var string
	 */
	protected static $table = 'privilege';

	/**
	 * @var array
	 */
	protected static $scheme = [
		'id' => ['type' => 'ID'],
		'privilege_name' => ['type' => 'STRING', 'length' => 64],
		'user_group_id' => ['type' => 'INTEGER'],
	];

	/**
	 * @var array
	 */
	protected static $u_keys = [['privilege_name', 'user_group_id']];

	/**
	 * @param string $name
	 * @param OPAM_User $user
	 * @return bool
	 */
	public static function hasPrivilege($name, $user)
	{
		$user_groups = $user->get('user_groups');
		if (!in_array(OPAM_User::GROUP_ADMIN, $user_groups)) {
			$user_groups[] = 0;
			$num = (new \Orange\Database\Queries\Select(self::$table))
				->addWhere(new Condition('privilege_name', '=', $name))
				->addWhere(new Condition('user_group_id', 'IN', $user_groups))
				->addField('id')
				->addField('privilege_name')
				->execute()
				->getResultNumRow();
			return $num > 0;
		} else {
			return true;
		}
	}

	/**
	 * @param array $data
	 */
	public static function massPrivilegesDeleting($data)
	{
		if ($data) {
			$delete = new \Orange\Database\Queries\Delete(self::$table);
			$first = true;
			foreach ($data as $group_id => $privileges) {
				if ($privileges) {
					if ($first) {
						$first = false;
					} else {
						$delete->addWhereOperator(Condition::L_OR);
					}
					$delete->addWhereBracket(true);
					$delete->addWhere(new Condition('user_group_id', '=', $group_id));
					$delete->addWhere(new Condition('privilege_name', 'IN', $privileges));
					$delete->addWhereBracket(false);
				}
			}
			$delete->execute();
		}
	}

	/**
	 * @param array $data
	 */
	public static function massPrivilegesAdding($data)
	{
		if ($data) {
			foreach ($data as $group_id => $privileges) {
				if ($privileges) {
					foreach ($privileges as $privilege) {
						$item = new OPAM_Privilege();
						$item->set('privilege_name', $privilege);
						$item->set('user_group_id', $group_id);
						$item->save();
					}
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public static function getPrivilegesByGroup()
	{
		$privileges = [];
		$result = (new \Orange\Database\Queries\Select(self::$table))
			->execute()
			->getResultArray();
		if ($result) {
			foreach ($result as $row) {
				if (!isset($privileges[$row['user_group_id']])) {
					$privileges[$row['user_group_id']] = [];
				}
				$privileges[$row['user_group_id']][] = $row['privilege_name'];
			}
		}
		return $privileges;
	}

}