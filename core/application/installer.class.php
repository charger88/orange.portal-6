<?php

abstract class OPAL_Installer {
	
	protected $module;
	
	protected $errors = array();
	
	public function __construct($module){
		$this->module = $module;
	}
	
	public function getErrors(){
		return $this->errors;
	}
	
	
}