<?php

class OPMI_News extends OPAL_Installer {
	
	public function installModule(){
		$this->errors = array();
		if (empty($this->errors)){
			$this->createThisModule();
		}
        if (empty($this->errors)){
            $this->createConfig(array(
                'categories' => 'LIST',
            ));
        }
        if (empty($this->errors)){
            $this->createContentTypes();
        }
        if (empty($this->errors)){
            $this->createContent();
        }
		return $this->errors;
	}
	
	private function createContentTypes(){
		$result = true;
		$content_types_data = array(
			array(
				'content_type_name'       => 'TYPE_NEWS_ITEM',
				'content_type_code'       => 'news_item',
				'content_type_type'       => 3,
				'content_type_multilang'  => true,
				'content_type_class'      => 'OPMM_News_Item',
				'content_type_hidden'     => array('content_order','content_area','content_on_site_mode','content_time_modified','content_user_id'),
				'content_type_fields'     => array(),
				'content_type_texts'      => array('excerpt' => 'ADMIN_MODULE_NEWS_EXCERPT','text' => 'ADMIN_MODULE_NEWS_TEXT'),
                'content_type_sitemap_priority' => 50,
			),
		);
		foreach ($content_types_data as $data){
			$content = new OPAM_Content_Type();
			$content->setData($data);
			$content->set('content_type_status', 1);
			$results[] = $content->save()->id;
		}
		return $result;
	}

    private function createContent(){
        $result = true;
        $content_data = array(
            array(
                'content_type'           => 'admin',
                'content_title'          => 'MODULE_NEWS',
                'content_access_groups'  => array(1,2),
                'content_lang'           => '',
                'content_slug'           => 'admin/news',
                'content_on_site_mode'   => 0,
                'content_status'         => 6,
                'content_commands'       => array( array( 'module' => 'news', 'controller' => 'admin-main', 'method' => '', 'static' => false, 'args' => array() ), ),
                'content_template'       => 'main-admin.phtml',
            ),
        );
        foreach ($content_data as $data){
            $content = new OPAM_Content();
            $content->setData($data);
            $content->set('content_time_published', time());
            $content->set('content_user_id', 1);
            $id = $content->save()->id;
            if (!$id){
                $result = false;
            }
        }
        return $result;
    }

    public function createdAdditionalContent(){
        $lang = OPAL_Portal::config('system_default_lang','en');
        OPAL_Lang::load('modules/news/lang/admin', $lang);
        $content = new OPAM_Block();
        $content->setData([
            'content_title'          => OPAL_Lang::t('MODULE_NEWS'),
            'content_access_groups'  => [0],
            'content_lang'           => '',
            'content_area'           => 'column',
            'content_slug'           => 'news-list',
            'content_on_site_mode'   => 3,
            'content_status'         => 6,
            'content_commands'       => [[ 'module' => 'news', 'controller' => 'main', 'method' => 'index', 'static' => true, 'args' => [] ], ],
            'content_template'       => 'block-portal.phtml',
            'content_user_id'        => 1,
        ]);
        $content->save();
        $content = new OPMM_News_Item();
        $content->setData([
            'content_title'          => OPAL_Lang::t('INSTALL_NEWS_TITLE_1'),
            'content_access_groups'  => [0],
            'content_lang'           => $lang,
            'content_slug'           => 'welcome-news.html',
            'content_on_site_mode'   => 0,
            'content_status'         => 6,
            'content_time_published' => time(),
            'content_commands'       => [[ 'module' => 'news', 'controller' => 'main', 'method' => 'view', 'static' => false, 'args' => [] ], ],
            'content_template'       => 'main-html.phtml',
            'content_user_id'        => 1,
        ]);
        $id = $content->save()->id;
        $text = new OPAM_Content_Text();
        $text->set('content_id', $id);
        $text->set('content_text_role', 'excerpt');
        $text->set('content_text_value', OPAL_Lang::t('INSTALL_NEWS_EXCERPT_1'));
        $text->save();
        $text = new OPAM_Content_Text();
        $text->set('content_id', $id);
        $text->set('content_text_role', 'text');
        $text->set('content_text_value', OPAL_Lang::t('INSTALL_NEWS_TEXT_1'));
        $text->save();
    }
	
}