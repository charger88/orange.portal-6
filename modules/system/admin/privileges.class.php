<?php

class OPMA_System_Privileges extends OPAL_Controller
{

	public function indexAction()
	{
		$privileges = [];
		$modules = OPAL_Module::getModules(true);
		foreach ($modules as $module) {
			$privileges = array_merge($privileges, $module->getPrivilegesList());
		}
		return $this->templater->fetch('system/admin-privileges.phtml', array(
			'privileges' => $privileges,
			'privileges_data' => OPAM_Privilege::getPrivilegesByGroup(),
			'groups' => OPAM_User_Group::getList(),
			'slug' => $this->content->getSlug(),
		));
	}

	public function saveAction()
	{
		$privileges_old = OPAM_Privilege::getPrivilegesByGroup();
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
			OPAM_Privilege::massPrivilegesDeleting($delete);
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
			OPAM_Privilege::massPrivilegesAdding($add);
		}
		return $this->msg(OPAL_Lang::t('%s_PRIVILEGES_ADDED__%s_PRIVILEGES_DELETED', array($addCounter, $deleteCounter)), self::STATUS_OK, $this->content->getURL());
	}

}