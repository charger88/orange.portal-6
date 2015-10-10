<?php

class OPMA_System_Modules extends OPAL_Controller {
		
	public function indexAction(){
		$list = OPAL_Module::getModules();
        foreach ($list as $id => $module) {
            $module->set('module_title',OPAL_Lang::t($module->get('module_title')));
            $list[$id] = $module;
        }
		return $this->templater->fetch('system/admin-modules.phtml',array('list' => $list));
    }

    public function newAction(){
        if ($list = OPAL_Module::getNotInstalledModules()) {
            foreach ($list as $id => $module) {
                $list[$id] = $module;
            }
            return $this->templater->fetch('system/admin-modules.phtml',array('list' => $list));
        } else {
            return $this->msg(OPAL_Lang::t('ADMIN_NOTHING_FOUND'),self::STATUS_NOTFOUND);
        }
    }

    public function switchAction($code){
        $module = new OPAM_Module('module_code',$code);
        $msg = OPAL_Lang::t('ADMIN_ERROR');
        $status = self::STATUS_ERROR;
        if ($module->id){
            if ($module->id === 1){
                $msg = OPAL_Lang::t('ADMIN_SYSTEM_MODULE_NOT_EDITABLE');
            } else {
                $module = $module->getModuleObject();
                if ($module->get('module_status')) {
                    $module->disable();
                    $msg = OPAL_Lang::t('ADMIN_DISABLED');
                    $status = self::STATUS_COMPLETE;
                } else {
                    $module->enable();
                    $msg = OPAL_Lang::t('ADMIN_ENABLED');
                    $status = self::STATUS_COMPLETE;
                }
            }
        }
        return $this->msg($msg,$status,OP_WWW.'/admin/modules');
    }

    public function installAction($code){
        $module = new OPAM_Module('module_code',$code);
        $msg = OPAL_Lang::t('ADMIN_ERROR');
        $status = self::STATUS_ERROR;
        if (!$module->id){
            $module->set('module_code',$code);
            $module = $module->getModuleObject();
            $module->install();
            $msg = OPAL_Lang::t('ADMIN_MODULE_INSTALLED');
            $status = self::STATUS_COMPLETE;
        }
        return $this->msg($msg,$status,OP_WWW.'/admin/modules');
    }

    public function uninstallAction($code){
        $module = new OPAM_Module('module_code',$code);
        $msg = OPAL_Lang::t('ADMIN_ERROR');
        $status = self::STATUS_ERROR;
        if ($module->id){
            $module = $module->getModuleObject();
            $module->uninstall();
            $msg = OPAL_Lang::t('ADMIN_MODULE_UNINSTALLED');
            $status = self::STATUS_COMPLETE;
        }
        return $this->msg($msg,$status,OP_WWW.'/admin/modules');
    }


    public function discoverAction(){
        return $this->msg(OPAL_Lang::t('Functionality is under construction.'),self::STATUS_NOTFOUND);
    }
	
}