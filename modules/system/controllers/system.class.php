<?php

class OPMC_System extends \Orange\Portal\Core\App\Controller
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
			'copyright' => \Orange\Portal\Core\App\Portal::config('system_copyright'),
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
			'content' => \Orange\Portal\Core\App\Portal::getInstance()->content,
			'adminBarLinks' => \Orange\Portal\Core\App\Portal::getInstance()->processHooks('admin_bar_links'),
		]);
	}

	/**
	 * Print language switcher
	 * @return string
	 */
	public function langswitcherBlock()
	{
		$default_lang = \Orange\Portal\Core\App\Portal::config('system_default_lang', '');
		$current_lang = \Orange\Portal\Core\App\Portal::$sitelang;
		$languages = \Orange\Portal\Core\App\Lang::langs();
		$enabled_languages = [];
		foreach (\Orange\Portal\Core\App\Portal::config('system_enabled_langs', []) as $lang){
			$enabled_languages[$lang] = $languages[$lang];
		}
		$pages = \Orange\Portal\Core\App\Portal::getInstance()->content->getLanguagePages($default_lang, $this->user);
		if (empty($pages)) {
			$page = clone \Orange\Portal\Core\App\Portal::getInstance()->content;
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
	public function headBlockDirect($install = 0)
	{
		$content = \Orange\Portal\Core\App\Portal::getInstance()->content;
		$data = [];
		$data['hidden'] = $content->field('seo_hidden');
		$data['title'] = $content->field('seo_title') ? $content->field('seo_title') : (\Orange\Portal\Core\App\Portal::config('system_sitename',null) ? $content->get('content_title') . ' - ' . \Orange\Portal\Core\App\Portal::config('system_sitename') : $content->get('content_title') );
		$data['name'] = $content->field('seo_title') ? $content->field('seo_title') : $content->get('content_title');
		$data['description'] = $content->field('seo_description') ? $content->field('seo_description') : ( $content->text('story')->get('content_text_value') ? strip_tags($content->text('story')->get('content_text_value')) : \Orange\Portal\Core\App\Portal::config('system_seo_description','') );
		$data['keywords'] = $content->field('seo_keywords') ? $content->field('seo_keywords') : ( \Orange\Portal\Core\App\Portal::config('system_seo_keywords','') );
		$data['author'] = \Orange\Portal\Core\App\Portal::config('system_copyright','');
		$data['url'] = count(\Orange\Portal\Core\App\Portal::getInstance()->getRequest()) <= 1 ? ( $content->field('seo_canonical') ? $content->field('seo_canonical') : OP_WWW.'/'.$content->getSlug(\Orange\Portal\Core\App\Portal::config('system_default_lang')) ) : null;
		$data['image'] = $content->get('content_image') ? OP_WWW.'/'.$content->getImageUrl('m') : $this->templater->theme->getShareImage();
		$data['alternate'] = (count(\Orange\Portal\Core\App\Portal::config('system_enabled_langs', [])) > 1)
			? $content->getLanguagePages(\Orange\Portal\Core\App\Portal::config('system_default_lang',''), \Orange\Portal\Core\App\Portal::getInstance()->user)
			: [];
		$data['styles'] = $this->templater->theme->getHeadStyleFiles();
		$data['scripts'] = $this->templater->theme->getHeadScriptFiles();
		$data['png_icon'] = (new \Orange\FS\File('sites/' . \Orange\Portal\Core\App\Portal::$sitecode . '/static/root/favicon.png'))->exists();
		return $this->templater->fetch('system/head.phtml', $data);
	}

}