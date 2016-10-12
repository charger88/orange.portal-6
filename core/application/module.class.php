<?php

abstract class OPAL_Module extends OPAM_Module {
	
	public function __construct($data = array()){
		if (!is_null($data)){
			$code = strtolower(substr(get_class($this),5));
			if (is_array($data) && !empty(is_array($data)) && ($data['module_code'] == $code)){
                parent::__construct($data);
			} else {
                parent::__construct('module_code',$code);
			}
		} else {
            parent::__construct();
        }
	}
	
	public function init(){
		if ($this->id && $this->get('module_status')){
			return $this->doInit();
		}
		return null;
	}
	
	public function getInstallForm(){
		return null;
	}
	
	public function installModule($params = array()){
		if (!$this->id && !$this->get('module_status')){
			return $this->doInstall($params);
		}
		return null;
	}
	
	public function enable(){
		if ($this->id && !$this->get('module_status')){
            $this->set('module_status',1);
            $this->save();
			return $this->doEnable();
		}
		return null;
	}
	
	public function disable(){
		if ($this->id && $this->get('module_status')){
            $this->set('module_status',0);
            $this->save();
			return $this->doDisable();
		}
		return null;
	}
	
	public function uninstallModule(){
		if ($this->id && !$this->get('module_status')){
            $this->delete();
			return $this->doUninstall();
		}
		return null;
	}
	
	protected abstract function doInit();

    protected abstract function doInstall($params);

    protected abstract function doEnable();

    protected function doDisable(){
        $this->set('module_status',0);
        $this->save();
        return null;
    }

    protected abstract function doUninstall();

    public abstract function getAdminMenu();

	protected $privileges = array();

    public function getInfo(){
        $info = array(
            'title'       => '',
            'description' => '',
            'code'        => '',
            'author'      => '',
            'author_url'  => '',
        );
        if ($this->get('module_code')) {
            $file = new \Orange\FS\File('modules/' . $this->get('module_code'), 'info.json');
            if ($file->exists()){
                $file = json_decode($file->getData(), true);
                foreach ($info as $key => $value) {
                    if (isset($file[$key])) {
                        $info[$key] = $file[$key];
                    }
                }
            }
        }
        return $info;
    }

	public function getPrivilegesList(){
		return array_unique(array_values($this->privileges));
	}
	
	public function getPrivilege($controllername,$methodname){
		$privilegename = $controllername.'::'.$methodname;
		return isset($this->privileges[$privilegename]) ? $this->privileges[$privilegename] : null;
	}
	
}