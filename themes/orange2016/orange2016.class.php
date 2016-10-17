<?php

class OPTF_Orange2016 extends OPTF_Default {

	public function __construct(){
		$theme_name = $this->getThemeNameByClass(__CLASS__);
		if (!$this->theme_name){
			$this->theme_name = $theme_name;
		}
		array_push($this->folders,$theme_name);
		parent::__construct();
	}
	
}