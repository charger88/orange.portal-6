<?php

class OPMA_System_Sitemap extends OPAL_Controller {

    public function rebuildAction(){
        self::rebuild();
        return $this->msg(OPAL_Lang::t('SITEMAP_WAS_REBUILT'), self::STATUS_OK, OP_WWW.'/admin');
    }

    public static function rebuild(){
        $index = new \Orange\Sitemap\Index();
        if ($sitemaps = OPAL_Portal::getInstance()->processHooks('adminCenter_sitemap')) {
            foreach ($sitemaps as $sitemapsByHook) {
                foreach ($sitemapsByHook as $sitemap_name => $lastmod) {
                    $index->addSitemap(OP_WWW.'/sitemap_'.$sitemap_name.'.xml', $lastmod);
                }
            }
        }
        $indexFile = new \Orange\FS\File('sites/'.OPAL_Portal::$sitecode.'/static/root', 'sitemap.xml');
        $indexFile->save($index->build());
    }

    public function sitemapHook(){
        $index = new \Orange\FS\File('sites/'.OPAL_Portal::$sitecode.'/static/root', 'sitemap.xml');
        $sitemap = simplexml_load_string($index->getData());
        $files = array();
        $files['sitemap.xml'] = array(
            'time' => $index->getModifyTime(),
            'items' => $sitemap ? count($sitemap) : 0,
        );
        if ($sitemap){
            foreach ($sitemap as $element) {
                $name = basename($element->loc);
                $sfile = new \Orange\FS\File('sites/'.OPAL_Portal::$sitecode.'/static/root', $name);
                if ($sfile){
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
        return $this->templater->fetch('system/admin-center-sitemap.phtml',array(
            'files' => $files,
        ));
    }

    public function buildSitemapHook(){
        $sitemaps = array();
        if ($types = OPAM_Content_Type::getTypesForSitemap()){
            $custom = OPAM_Content_Field::getRef('seo_sitemap_priority');
            foreach ($types as $sitemap_name => $priority) {
                $list = OPAM_Content::getList(array(
                    'types' => array($sitemap_name),
                    'access_user' => new OPAM_User(),
                    'fields'      => [
                        'seo_hidden' => '1',
                        'seo_sitemap_priority' => '-1',
                    ],
                    'fields_not'  => true,
                ),'OPAM_Content');
                $lTime = 0;
                $indexFile = new \Orange\FS\File('sites/'.OPAL_Portal::$sitecode.'/static/root', 'sitemap_'.$sitemap_name.'.xml');
                if ($list) {
                    $sitemap = new \Orange\Sitemap\Urlset();
                    $count = 0;
                    foreach ($list as $item) {
                        if (!isset($custom[$item->id])){
                            $custom[$item->id] = 0;
                        }
                        if (($custom[$item->id] > 0) || ( ($priority > 0) && ($custom[$item->id] >= 0) )) {
                            $iPriority = $custom[$item->id] > 0 ? $custom[$item->id] : $priority;
                            //TODO Add support of "Change frequency"
                            $sitemap->addUrl(OP_WWW . '/' . $item->getSlug(OPAL_Portal::config('system_default_lang')), $cTime = $item->get('content_time_modified'), null, $iPriority / 100);
                            $count++;
                            if (strtotime($cTime) > $lTime) {
                                $lTime = $cTime;
                            }
                        }
                    }
                    if ($count) {
                        $indexFile->save($sitemap->build());
                        $sitemaps[$sitemap_name] = $lTime;
                    } else {
                        $indexFile->remove();
                    }
                } else {
                    $indexFile->remove();
                }

            }
        }
        return $sitemaps;
    }
	
}