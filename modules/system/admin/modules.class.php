<?php

class OPMA_System_Modules extends \Orange\Portal\Core\App\Controller
{

	public function indexAction()
	{
		$list = \Orange\Portal\Core\App\Module::getModules();
		foreach ($list as $id => $module) {
			$module->set('module_title', \Orange\Portal\Core\App\Lang::t($module->get('module_title')));
			$list[$id] = $module;
		}
		return $this->templater->fetch('system/admin-modules.phtml', array('list' => $list));
	}

	public function newAction()
	{
		if ($list = \Orange\Portal\Core\App\Module::getNotInstalledModules()) {
			foreach ($list as $id => $module) {
				$list[$id] = $module;
			}
			return $this->templater->fetch('system/admin-modules.phtml', array('list' => $list));
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_NOTHING_FOUND'), self::STATUS_NOTFOUND);
		}
	}

	public function switchAction($code)
	{
		$module = new \Orange\Portal\Core\Model\Module('module_code', $code);
		$msg = \Orange\Portal\Core\App\Lang::t('ADMIN_ERROR');
		$status = self::STATUS_ERROR;
		if ($module->id) {
			if ($module->id === 1) {
				$msg = \Orange\Portal\Core\App\Lang::t('ADMIN_SYSTEM_MODULE_NOT_EDITABLE');
			} else {
				$module = $module->getModuleObject();
				if ($module->get('module_status')) {
					$module->disable();
					$msg = \Orange\Portal\Core\App\Lang::t('ADMIN_DISABLED');
					$status = self::STATUS_COMPLETE;
					\Orange\Portal\Core\App\Portal::getInstance()->cache->remove('modules_', true);
				} else {
					$module->enable();
					$msg = \Orange\Portal\Core\App\Lang::t('ADMIN_ENABLED');
					$status = self::STATUS_COMPLETE;
				}
			}
		}
		return $this->msg($msg, $status, OP_WWW . '/admin/modules');
	}

	public function installAction($code)
	{
		$module = new \Orange\Portal\Core\Model\Module('module_code', $code);
		$msg = \Orange\Portal\Core\App\Lang::t('ADMIN_ERROR');
		$status = self::STATUS_ERROR;
		if (!$module->id) {
			$module->set('module_code', $code);
			$module = $module->getModuleObject();
			$errors = $module->installModule();
			if (empty($errors)) {
				$msg = \Orange\Portal\Core\App\Lang::t('ADMIN_MODULE_INSTALLED');
				$status = self::STATUS_COMPLETE;
				\Orange\Portal\Core\App\Portal::getInstance()->cache->remove('modules_', true);
			} else {
				$msg = \Orange\Portal\Core\App\Lang::t('ADMIN_MODULE_FAILED') . ': ' . implode(', ', $errors) . '.';
				$status = self::STATUS_ERROR;
			}
		}
		return $this->msg($msg, $status, OP_WWW . '/admin/modules');
	}

	public function uninstallAction($code)
	{
		$module = new \Orange\Portal\Core\Model\Module('module_code', $code);
		$msg = \Orange\Portal\Core\App\Lang::t('ADMIN_ERROR');
		$status = self::STATUS_ERROR;
		if ($module->id) {
			$module = $module->getModuleObject();
			$module->uninstallModule();
			$msg = \Orange\Portal\Core\App\Lang::t('ADMIN_MODULE_UNINSTALLED');
			$status = self::STATUS_COMPLETE;
		}
		\Orange\Portal\Core\App\Portal::getInstance()->cache->remove('modules_', true);
		return $this->msg($msg, $status, OP_WWW . '/admin/modules');
	}


	public function discoverAction()
	{
		return $this->msg(\Orange\Portal\Core\App\Lang::t('Functionality is under construction.'), self::STATUS_NOTFOUND);
	}

}