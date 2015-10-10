<?php

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
		return $i->install();
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
        $select = new OPDB_Select('content');
        $select->addWhere(new OPDB_Clause('content_type','=','news_item'));
        $select = $select->execQuery();
        while ($item = $select->getNext()){
            $item = new OPMM_News_Item($item);
            $item->delete();
        }
        $admin = new OPDB_Select('content');
        $admin->addWhere(new OPDB_Clause('content_slug','=','admin/news'));
        $admin = new OPAM_Admin($admin->execQuery()->getNext());
        $admin->delete();
        $content_type = new OPAM_Content_Type('content_type_code','news_item');
        $content_type->delete();
		return null;
	}

	public function getAdminMenu(){
        OPAL_Lang::load('modules/news/lang', OPAL_Portal::$sitelang);
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
				)
			),
		);
		return $adminMenu;
	}
	
}