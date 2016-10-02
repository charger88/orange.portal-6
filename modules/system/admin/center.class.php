<?php

class OPMA_System_Center extends OPAL_Controller {
	
	public function indexAction(){
		return $this->templater->fetch('system/admin-center.phtml',array(
			'blocks' => OPAL_Portal::getInstance()->processHooks('adminCenter_index'),
		));
	}
	
	public function licenseAction(){
		$license = new \Orange\FS\File('license.txt');
		return $this->templater->fetch('system/admin-center-license.phtml',array(
			'license' => $license->getData(),
		));
	}

	public function systemHook(){
		$version = new \Orange\FS\File('core', 'version.txt');
		return $this->templater->fetch('system/admin-center-system.phtml',array(
			'version' => $version->getData(),
			'server' => $this->getServer('SERVER_SOFTWARE',''),
		));
	}

	public function menuBlockDirect(){
		$menu = array();
		if ($modules = OPAM_Module::getModules(true)){
			foreach ($modules as $module){
				if ($module_menu = $module->getAdminMenu()){
					$menu = array_merge($menu,$module_menu);
				}
			}
		}
		return $this->templater->fetch('system/admin-menu.phtml',array(
			'menu' => $menu,
		));
	}
	
}