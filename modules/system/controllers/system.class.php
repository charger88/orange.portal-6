<?php

class OPMC_System extends OPAL_Controller
{

	/**
	 * Print copyrights
	 * @return string
	 */
	public function copyrightsAction()
	{
		return $this->copyrights();
	}

	/**
	 * Print copyrights
	 * @return string
	 */
	public function copyrightsAjax()
	{
		return $this->copyrights();
	}

	/**
	 * Print copyrights
	 * @return string
	 */
	public function copyrightsBlock()
	{
		return $this->copyrights();
	}

	/**
	 * Copyrights functionality implementation
	 * @return string
	 */
	private function copyrights()
	{
		return $this->templater->fetch('system/' . $this->arg('prefix', 'default') . '-copyrights' . '.phtml', array(
			'copyright' => OPAL_Portal::config('system_copyright'),
			'year_opened' => $this->arg('year_opened', date('Y')),
			'theme' => $this->templater->theme->getThemeInfo(),
		));
	}

	/**
	 * Print administrator's bar on front-end
	 * @return string
	 */
	public function adminbarBlock()
	{
		return $this->templater->fetch('system/' . $this->arg('prefix', 'default') . '-admin-bar.phtml', array(
			'content' => OPAL_Portal::getInstance()->content,
			'adminBarLinks' => OPAL_Portal::getInstance()->processHooks('admin_bar_links'),
		));
	}

	/**
	 * Print language switcher
	 * @return string
	 */
	public function langswitcherBlock()
	{
		$default_lang = OPAL_Portal::config('system_default_lang', '');
		$current_lang = OPAL_Portal::$sitelang;
		$languages = OPAL_Lang::langs(OPAL_Portal::config('system_enabled_langs', []));
		$pages = OPAL_Portal::getInstance()->content->getLanguagePages($default_lang, $this->user);
		if (empty($pages)) {
			$page = clone OPAL_Portal::getInstance()->content;
			$page->set('content_lang', '');
			$pages = ['' => $page];
		}
		$get_string = '';
		if ($get = $this->getGetArray()) {
			if (isset($get['lang'])) {
				unset($get['lang']);
			}
			foreach ($get as $param => $value) {
				$get_string .= urlencode($param) . '=' . urlencode($value) . '&';
			}
		}
		return $this->templater->fetch('system/' . $this->arg('prefix', 'default') . '-lang-switcher.phtml', array(
			'languages' => $languages,
			'pages' => $pages,
			'default_lang' => $default_lang,
			'current_lang' => $current_lang,
			'get_string' => $get_string,
		));
	}

}