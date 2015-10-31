<?php

class OPMI_News extends OPAL_Installer {
	
	public function install(){
		$this->errors = array();
		if (empty($this->errors)){
			$this->createThisModule();
		}
        if (empty($this->errors)){
            $this->createContentTypes();
        }
        if (empty($this->errors)){
            $this->createContent();
        }
		return $this->errors;
	}
	
	private function createThisModule(){
		$result = true;
		$module = new OPAM_Module();
		$module->setFromArray(array(
			'module_code'   => 'news',
			'module_title'  => 'MODULE_NEWS',
			'module_status' => true,
		));
		$id = $module->save();
		return $result;
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
			$content->setFromArray($data);
			$content->set('content_type_status', 1);
			$results[] = $content->save();
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
            $content->setFromArray($data);
            $content->set('content_time_published', OPDB_Functions::getTime());
            $content->set('content_user_id', 1);
            $id = $content->save();
            if (!$id){
                $result = false;
            }
        }
        return $result;
    }
	
}