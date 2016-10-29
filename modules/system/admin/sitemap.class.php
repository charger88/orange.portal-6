<?php

class OPMA_System_Sitemap extends OPAL_Controller
{

	public function rebuildAction()
	{
		self::rebuild();
		return $this->msg(OPAL_Lang::t('SITEMAP_WAS_REBUILT'), self::STATUS_OK, OP_WWW . '/admin');
	}

	public static function rebuild()
	{
		$index = new \Orange\Sitemap\Index();
		if ($sitemaps = OPAL_Portal::getInstance()->processHooks('build_sitemaps')) {
			foreach ($sitemaps as $sitemapsByHook) {
				foreach ($sitemapsByHook as $sitemap_name => $lastmod) {
					$index->addSitemap(OP_WWW . '/sitemap_' . $sitemap_name . '.xml', $lastmod);
				}
			}
		}
		$indexFile = new \Orange\FS\File('sites/' . OPAL_Portal::$sitecode . '/static/root', 'sitemap.xml');
		$indexFile->save($index->build());
	}

	public function sitemapBlockDirect()
	{
		$index = new \Orange\FS\File('sites/' . OPAL_Portal::$sitecode . '/static/root', 'sitemap.xml');
		if ($index->exists()) {
			$sitemap = simplexml_load_string($index->getData());
			$files = array();
			$files['sitemap.xml'] = array(
				'time' => $index->getModifyTime(),
				'items' => $sitemap ? count($sitemap) : 0,
			);
			if ($sitemap) {
				foreach ($sitemap as $element) {
					$name = basename($element->loc);
					$sfile = new \Orange\FS\File('sites/' . OPAL_Portal::$sitecode . '/static/root', $name);
					if ($sfile) {
						$sitemapXML = simplexml_load_string($sfile->getData());
						$items = $sitemapXML ? count($sitemapXML) : 0;
					} else {
						$items = -1;
					}
					$files[$name] = array(
						'time' => $sfile->getModifyTime(),
						'items' => $items,
					);
				}
			}
		} else {
			$files = [];
		}
		return $this->templater->fetch('system/admin-center-sitemap.phtml', array(
			'files' => $files,
		));
	}

}