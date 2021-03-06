<?php

class OPMA_System_Center extends \Orange\Portal\Core\App\Controller
{

	public function indexAction()
	{
		return $this->templater->fetch('system/admin-center.phtml', array(
			'blocks' => \Orange\Portal\Core\App\Portal::getInstance()->processHooks('admin_center_index'),
		));
	}

	public function licenseAction()
	{
		$license = new \Orange\FS\File('license.txt');
		return $this->templater->fetch('system/admin-center-license.phtml', array(
			'license' => $license->getData(),
		));
	}

	public function systemBlockDirect()
	{
		$version = new \Orange\FS\File('version.txt');
		return $this->templater->fetch('system/admin-center-system.phtml', array(
			'version' => $version->getData(),
			'server' => $this->getServer('SERVER_SOFTWARE', ''),
		));
	}

	public function menuBlockDirect()
	{
		$menu = array();
		if ($modules = \Orange\Portal\Core\Model\Module::getModules(true)) {
			foreach ($modules as $module) {
				if ($module_menu = $module->getAdminMenu()) {
					$menu = array_merge($menu, $module_menu);
				}
			}
		}
		return $this->templater->fetch('system/admin-menu.phtml', array(
			'menu' => $menu,
		));
	}

}