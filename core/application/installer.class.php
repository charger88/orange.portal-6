<?php

abstract class OPAL_Installer {
	
	protected $module;

    protected $params = array();

	protected $errors = array();
	
	public function __construct($module){
		$this->module = $module;
	}

    /**
     * @param \Orange\Database\ActiveRecord[]
     * @return bool
     */
    protected function createTables($classes){
        $result = true;
        $success = array();
        $errors = array();
        foreach ($classes as $classname){
            try {
                $classname::install();
            } catch (\Orange\Database\DBException $e){
                $result = false;
                $errors[] = $e->getMessage().' --- '.$e->getTraceAsString();
            }
            $success[] = $classname;
        }
        if (!$result){
            $this->errors['db_prefix'] = implode("\n",$errors);
            if ($success){
                foreach ($success as $classname){
                    (new \Orange\Database\Queries\Table\Drop($classname::getTableName()))
                        ->setIfExistsOnly()
                        ->execute()
                    ;
                }
            }
        }
        return $result;
    }

    protected function createConfig($params){
        $result = true;
        if ($params){
            foreach ($params as $param => $type){
                if  ($type == 'array'){
                    $this->params[$param] = json_encode($this->params[$param]);
                }
                $c = new OPAM_Config();
                $c->set('config_type', $type);
                $c->set('config_key', $this->module.'_'.$param);
                $c->set('config_value', isset($this->params[$param]) ? $this->params[$param] : null);
                $id = $c->save()->id;
                $result = $result && ($id > 0);
            }
        }
        return $result;
    }

	public function getErrors(){
		return $this->errors;
	}
	
	
}