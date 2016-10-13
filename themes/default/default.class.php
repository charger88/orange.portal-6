<?php

class OPTF_Default extends OPAL_Theme {
	
	public function __construct(){
		$theme_name = $this->getThemeNameByClass(__CLASS__);
		if (!$this->theme_name){
			$this->theme_name = $theme_name;
		}
		array_push($this->folders,$theme_name);
		parent::__construct();
	}
	
	public function getThemeAreas(){
		return [
			'header'      => OPAL_Lang::t('Header'),
			'sub-header'  => OPAL_Lang::t('Place under the header'),
			'top-section' => OPAL_Lang::t('Place between header and sub-header'),
			'column'      => OPAL_Lang::t('Left column'),
			'footer'      => OPAL_Lang::t('Footer'),
		];
	}
	
}