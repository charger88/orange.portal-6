<?php

/**
 * Class OPAM_Module
 */
class OPAM_Module extends OPDB_Object {

    /**
     * @var string
     */
    protected static $table = 'module';

    /**
     * @var array
     */
    protected static $schema = array(
		'id'             => array(0 ,'ID'),
		'module_code'    => array('','VARCHAR',32),
		'module_title'   => array('','VARCHAR',128),
		'module_status'  => array(0 ,'BOOLEAN'),
	);

    /**
     * @var array
     */
    protected static $indexes = array('module_status');
    /**
     * @var array
     */
    protected static $uniq = array('module_code');

    /**
     * @param bool $active_only
     * @return OPAL_Module[]
     */
    public static function getModules($active_only = false){
		$modules = array();
		$select = new OPDB_Select(self::$table);
		if ($active_only){
			$select->addWhere(new OPDB_Clause('module_status','=',1));
		}
		$select->setOrder('id');
		if ($result = $select->execQuery()->getResultArray(false,__CLASS__)){
			foreach ($result as $module){
                /** @var OPAM_Module $module */
                if ($moduleObject = $module->getModuleObject()) {
                    $modules[] = $moduleObject;
                }
			}
		}
		return $modules;
	}

    /**
     * @return OPAL_Module[]
     */
    public static function getNotInstalledModules(){
        $modules = array();
        $modulesDir = new OPAL_File('modules');
        $dirs = $modulesDir->dirFiles();
        foreach ($dirs as $dirName){
            $dir = new OPAL_File($dirName,'modules');
            if ($dir->dir){
                $module = new OPAM_Module('module_code',$dirName);
                if (!$module->id) {
                    $module->set('module_code',$dirName);
                    $modules[] = $module->getModuleObject();
                }
            }
        }
        return $modules;
    }

    /**
     * @return OPAL_Module|null
     */
    public function getModuleObject(){
		if ($classname = $this->get('module_code')){
			$classname{0} = strtoupper($classname{0});
			$classname = 'OPMO_'.$classname;
			return new $classname($this->getDataArray());
		} else {
			return null;
		}
	}
	
	
}