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
		return $this->templater->fetch('system/' . $this->arg('prefix', 'default') . '-copyrights' . '.phtml', [
			'copyright' => OPAL_Portal::config('system_copyright'),
			'year_opened' => $this->arg('year_opened', date('Y')),
			'theme' => $this->templater->theme->getThemeInfo(),
		]);
	}

	/**
	 * Print administrator's bar on front-end
	 * @return string
	 */
	public function adminbarBlock()
	{
		return $this->templater->fetch('system/' . $this->arg('prefix', 'default') . '-admin-bar.phtml', [
			'content' => OPAL_Portal::getInstance()->content,
			'adminBarLinks' => OPAL_Portal::getInstance()->processHooks('admin_bar_links'),
		]);
	}

	/**
	 * Print language switcher
	 * @return string
	 */
	public function langswitcherBlock()
	{
		$default_lang = OPAL_Portal::config('system_default_lang', '');
		$current_lang = OPAL_Portal::$sitelang;
		$languages = OPAL_Lang::langs();
		$enabled_languages = [];
		foreach (OPAL_Portal::config('system_enabled_langs', []) as $lang){
			$enabled_languages[$lang] = $languages[$lang];
		}
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
		return $this->templater->fetch('system/' . $this->arg('prefix', 'default') . '-lang-switcher.phtml', [
			'languages' => $enabled_languages,
			'pages' => $pages,
			'default_lang' => $default_lang,
			'current_lang' => $current_lang,
			'get_string' => $get_string,
		]);
	}

	/**
	 * Print language switcher
	 * @return string
	 */
	public function headBlockDirect()
	{
		$content = OPAL_Portal::getInstance()->content;
		$data = [];
		$data['hidden'] = $content->field('seo_hidden');
		$data['title'] = $content->field('seo_title') ? $content->field('seo_title') : (OPAL_Portal::config('system_sitename',null) ? $content->get('content_title') . ' - ' . OPAL_Portal::config('system_sitename') : $content->get('content_title') );
		$data['name'] = $content->field('seo_title') ? $content->field('seo_title') : $content->get('content_title');
		$data['description'] = $content->field('seo_description') ? $content->field('seo_description') : ( $content->text('story')->get('content_text_value') ? strip_tags($content->text('story')->get('content_text_value')) : OPAL_Portal::config('system_seo_description','') );
		$data['keywords'] = $content->field('seo_keywords') ? $content->field('seo_keywords') : ( OPAL_Portal::config('system_seo_keywords','') );
		$data['author'] = OPAL_Portal::config('system_copyright','');
		$data['url'] = count(OPAL_Portal::getInstance()->getRequest()) <= 1 ? ( $content->field('seo_canonical') ? $content->field('seo_canonical') : OP_WWW.'/'.$content->getSlug(OPAL_Portal::config('system_default_lang')) ) : null;
		$data['image'] = $content->get('content_image') ? OP_WWW.'/'.$content->getImageUrl('m') : $this->templater->theme->getShareImage();
		$data['alternate'] = (count(OPAL_Portal::config('system_enabled_langs', [])) > 1)
			? $content->getLanguagePages(OPAL_Portal::config('system_default_lang',''), OPAL_Portal::getInstance()->user)
			: [];
		$data['styles'] = $this->templater->theme->getHeadStyleFiles();
		$data['scripts'] = $this->templater->theme->getHeadScriptFiles();
		$data['png_icon'] = (new \Orange\FS\File('sites/' . OPAL_Portal::$sitecode . '/static/root/favicon.png'))->exists();
		return $this->templater->fetch('system/head.phtml', $data);
	}

}