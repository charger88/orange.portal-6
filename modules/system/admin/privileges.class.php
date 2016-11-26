<?php

class OPMA_System_Privileges extends \Orange\Portal\Core\App\Controller
{

	public function indexAction()
	{
		$privileges = [];
		$modules = \Orange\Portal\Core\App\Module::getModules(true);
		foreach ($modules as $module) {
			$privileges = array_merge($privileges, $module->getPrivilegesList());
		}
		return $this->templater->fetch('system/admin-privileges.phtml', array(
			'privileges' => $privileges,
			'privileges_data' => \Orange\Portal\Core\Model\Privilege::getPrivilegesByGroup(),
			'groups' => \Orange\Portal\Core\Model\UserGroup::getList(),
			'slug' => $this->content->getSlug(),
		));
	}

	public function saveAction()
	{
		$privileges_old = \Orange\Portal\Core\Model\Privilege::getPrivilegesByGroup();
		$privileges_new = $this->getPost('p');
		$delete = $add = array();
		$deleteCounter = $addCounter = 0;
		if ($privileges_old) {
			foreach ($privileges_old as $group_id => $group_data) {
				if (isset($privileges_new[$group_id])) {
					foreach ($group_data as $group_privilege) {
						if (!in_array($group_privilege, $privileges_new[$group_id])) {
							if (!isset($delete[$group_id])) {
								$delete[$group_id] = array();
							}
							$delete[$group_id][] = $group_privilege;
							$deleteCounter++;
						}
					}
				} else {
					$delete[$group_id] = array_merge($delete, $group_data);
					$deleteCounter += count($group_data);
				}
			}
			\Orange\Portal\Core\Model\Privilege::massPrivilegesDeleting($delete);
		}
		if ($privileges_new) {
			foreach ($privileges_new as $group_id => $group_data) {
				if (isset($privileges_old[$group_id])) {
					foreach ($group_data as $group_privilege) {
						if (!in_array($group_privilege, $privileges_old[$group_id])) {
							if (!isset($add[$group_id])) {
								$add[$group_id] = array();
							}
							$add[$group_id][] = $group_privilege;
							$addCounter++;
						}
					}
				} else {
					$add[$group_id] = $group_data;
					$addCounter += count($group_data);
				}
			}
			\Orange\Portal\Core\Model\Privilege::massPrivilegesAdding($add);
		}
		return $this->msg(\Orange\Portal\Core\App\Lang::t('%s_PRIVILEGES_ADDED__%s_PRIVILEGES_DELETED', array($addCounter, $deleteCounter)), self::STATUS_OK, $this->content->getURL());
	}

}