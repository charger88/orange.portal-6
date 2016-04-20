<?php

class OPMC_System extends OPAL_Controller {

	/**
	 * Print copyrights
	 * @return string
	 */
	public function copyrightsAction(){
		return $this->copyrights();
	}

	/**
	 * Print copyrights
	 * @return string
	 */
	public function copyrightsAjax(){
		return $this->copyrights();
	}

	/**
	 * Print copyrights
	 * @return string
     */
	public function copyrightsBlock(){
		return $this->copyrights();
	}

    /**
     * Copyrights functionality implementation
     * @return string
     */
	private function copyrights(){
		return $this->templater->fetch('system/'.$this->arg('prefix','default').'-copyrights'.'.phtml',array(
			'copyright'   => OPAL_Portal::config('system_copyright'),
			'year_opened' => $this->arg('year_opened',date('Y')),
			'theme'       => $this->templater->theme->getThemeInfo(),
		));
	}

    public static $adminBarLinks = [];

	/**
	 * Print administrator's bar on front-end
	 * @return string
	 */
	public function adminbarBlock(){
		return $this->templater->fetch('system/'.$this->arg('prefix','default').'-admin-bar.phtml',array(
			'content'  => OPAL_Portal::getInstance()->content,
            'adminBarLinks' => self::$adminBarLinks,
		));
	}

	/**
	 * Print language switcher
	 * @return string
	 */
	public function langswitcherBlock(){
		$pages = OPAL_Portal::getInstance()->content->getLanguagePages(OPAL_Portal::config('system_default_lang',''),$this->user);
		if (isset($pages['']) && ($pages[''] == '')){
			$request = str_replace('&&', '&', str_replace('?&', '?', str_replace('lang='.OPAL_Portal::$sitelang, '', OPAL_Portal::env('request'))));
			$pages[''] = OPAL_Portal::env('protocol') . '://' .OPAL_Portal::env('hostname') . $request;
		}
		return $this->templater->fetch('system/'.$this->arg('prefix','default').'-lang-switcher.phtml',array(
			'languages' => OPAL_Lang::langs(OPAL_Portal::config('system_enabled_langs',array())),
			'pages' => $pages,
		));
	}
	
}