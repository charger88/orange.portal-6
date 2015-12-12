<?php

abstract class OPAL_Installer {
	
	protected $module;
	
	protected $errors = array();
	
	public function __construct($module){
		$this->module = $module;
	}

    /**
     * @param OPDB_Object[]
     * @return bool
     */
    protected function createTables($objects){
        $result = true;
        $success = array();
        $errors = array();
        /** @var OPDB_Object $obj */
        foreach ($objects as $obj){
            $table = new OPDB_Table($obj->getStructure());
            list($t_result,$message) = $table->createTable();
            if ($t_result){
                $success[] = $table;
            } else {
                $result = false;
                $errors[] = $message;
            }
        }
        if (!$result){
            $this->errors['db_prefix'] = implode(' ', $errors);
            if ($success){
                /** @var OPDB_Table $table */
                foreach ($success as $table){
                    $table->dropTable();
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
                $c->set('config_value', $this->params[$param]);
                $id = $c->save();
                $result = $result && ($id > 0);
            }
        }
        return $result;
    }

	public function getErrors(){
		return $this->errors;
	}
	
	
}