<?php

use \Orange\Database\Queries\Parts\Condition;

class OPMO_News extends OPAL_Module {
	
	protected $privileges = array();

	protected function doInit(){
		$this->initHooks();
		return true;
	}

	private function initHooks(){

	}

    protected function doInstall($params = array()){
		$i = new OPMI_News('news');
		return empty($i->installModule($params));
	}

    protected function doEnable(){
		return null;
	}

    protected function doDisable(){
        $this->set('module_status',0);
        $this->save();
        $content_type = new OPAM_Content_Type('content_type_code','news_item');
        $content_type->set('content_type_status',false);
        $content_type->save();
		return null;
	}

    protected function doUninstall(){
        $select = new \Orange\Database\Queries\Select('content');
        $select->addWhere(new Condition('content_type','=','news_item'));
        $select = $select->execute();
        while ($item = $select->getResultNextRow()){
            $item = new OPMM_News_Item($item);
            $item->delete();
        }
        $admin = new \Orange\Database\Queries\Select('content');
        $admin->addWhere(new Condition('content_slug','=','admin/news'));
        $admin = new OPAM_Admin($admin->execute()->getResultNextRow());
        $admin->delete();
        $content_type = new OPAM_Content_Type('content_type_code','news_item');
        $content_type->delete();
        $config_option = new OPAM_Config('config_key','news_categories');
        $config_option->delete();
		return null;
	}

	public function getAdminMenu(){
		$adminMenu = array(
			'news' => array(
				'name' => 'MODULE_NEWS',
				'url' => '/admin/news',
				'icon' => '/modules/news/static/icons/news.png',
				'order' => 15,
				'sub' => array(
                    'tree' => array(
                        'name' => 'MODULE_NEWS_LIST',
                        'url' => '/admin/news',
                        'icon' => '/modules/news/static/icons/news.png',
                        'order' => 10
                    ),
                    'add' => array(
                        'name' => 'ADMIN_ADD_NEW',
                        'url' => '/admin/news/new',
                        'icon' => '/modules/news/static/icons/news-add.png',
                        'order' => 20
                    ),
                    'categories' => array(
                        'name' => 'ADMIN_CATEGORIES',
                        'url' => '/admin/news/categories',
                        'icon' => '/modules/system/static/icons/categories.png',
                        'order' => 30
                    ),
				)
			),
		);
		return $adminMenu;
	}
	
}