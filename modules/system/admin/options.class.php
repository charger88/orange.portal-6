<?php

class OPMA_System_Options extends OPAL_Controller {
	
	public function indexAction(){
		$options = array();
		if ($modules = OPAM_Module::getModules(true)){
			foreach ($modules as $module){
                if ($module_menu = $module->getAdminMenu()){
					if (isset($module_menu['options']) && !empty($module_menu['options']['sub'])){
						$options = array_merge($options,$module_menu['options']['sub']);
					}
				}
			}
		}
		return $this->templater->fetch('system/admin-options-index.phtml',array(
			'options' => $options
		));
	}
	
	public function systemAction(){
		$form = new OPMX_System_Options();
        $form->setAction(OP_WWW.'/admin/options/save');
		$form->setValues(OPAM_Config::loadActive('system'));
		return $form->getHTML($this->templater);
	}
	
	public function saveAction(){
		$this->saveOptions(new OPMX_System_Options());
		return $this->msg(OPAL_Lang::t('ADMIN_SAVED'), self::STATUS_OK, OP_WWW.'/admin/options/system');
	}
	
	public function moveAction(){
		$form = new OPMX_System_Move();
        $form->setAction(OP_WWW.'/admin/options/moveSite');
		$form->setValues(OPAM_Config::loadActive('system'));
		return $form->getHTML($this->templater);
	}
	
	public function moveSiteAction(){
		$oldDomain = OPAL_Portal::config('system_domain','');
		$oldBasedir = OPAL_Portal::config('system_base_dir','');
		$form = new OPMX_System_Move();
		$form->setValues($_POST);
		if ($data = $form->getValues()){
			$newDomain = $data['system_domain'];
			$newBasedir = $data['system_base_dir'];
			$newUrlChecking = parse_url(OPAL_Portal::env('protocol').'://'.$newDomain.'/'.$newBasedir);
			if (isset($newUrlChecking['host'])){
				$newDomain = $newUrlChecking['host'];
				$newBasedir = trim($newUrlChecking['path'],'/');
				$newUrl = OPAL_Portal::env('protocol').'://'.$newDomain.'/'.$newBasedir;
				if ( ($newDomain != $oldDomain) || ($newBasedir != $oldBasedir) ){
					$config = new \Orange\FS\Dir('sites', $oldDomain);
					if ($config->exists()){
                        $config->rename($newDomain);
					}
                    $config = new \Orange\FS\Dir('sites', $newDomain);
					if ($config->exists()){
						$this->saveOptions($form);
						return $this->msg(OPAL_Lang::t('ADMIN_MOVED_SUCCESSFUL'), self::STATUS_COMPLETE, $newUrl);
					} else {
						return $this->msg(OPAL_Lang::t('ADMIN_MOVED_FAIL'), self::STATUS_ERROR, OP_WWW.'/admin/options/move');
					}
				} else {
					return $this->msg(OPAL_Lang::t('ADMIN_MOVED_NOT_CHANGED'), self::STATUS_INFO, OP_WWW.'/admin/options/move');
				}
			} else {
				return $this->msg(OPAL_Lang::t('ADMIN_MOVED_INCORRECT_DATA'), self::STATUS_ERROR, OP_WWW.'/admin/options/move');
			}
		} else {
			return $this->msg(OPAL_Lang::t('ADMIN_ERROR'), self::STATUS_ERROR, OP_WWW.'/admin/options/move');
		}
	}

    /**
     * @param \Orange\Forms\Form $form
     */
    public function saveOptions($form){
		$form->setValues($_POST);
		if ($data = $form->getValues()){
            unset($data['content_edit_submit']);
			foreach ($data as $key => $value){
				$config = new OPAM_Config('config_key',$key);
				if ($config->id){
					if ($config->get('config_type') == 'boolean'){
						$value = $value ? 1 : 0;
					} else if ($config->get('config_type') == 'array'){
						$value = is_array($value) ? json_encode($value) : $value;
					}
					$config->set('config_value', $value);
					$config->save();
				} else {
					$this->log('ALERT_CONFIG_UNKNOWN_SAVE:%s',array($key),'LOG_OPTIONS',self::STATUS_ALERT);
				}
			}
		}
	}

}