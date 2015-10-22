<?php

class OPMA_System_Pages extends OPMA_System_Content {

	protected $content_type = 'page';
	protected $allowed_type_type = 1;
	
	public function indexAction(){
		return $this->wrapContentWithTemplate(
			$this->wrapper,
			$this->templater->fetch('system/admin-pages-tree.phtml',array(
				'tree' => OPAM_Page::getPagesByParents($this->user,true),
				'refs' => $this->getFormOptions(),
				'slug' => $this->content->getSlug(),
			))
		);
	}
	
	public function newAction($type = null){
		$type = new OPAM_Content_Type('content_type_code',$type ? $type : 'page');
		$classname = $type->getClass();
        /** @var OPAM_Page $item */
        $item = new $classname();
		$item->set('content_type',$type->get('content_type_code'));
		if ($item->isNewAllowed()){
			$item->set('content_time_published',OPDB_Functions::getTime());
			$item->set('content_template','main-html.phtml');
            $item->set('content_commands',array( array( 'module' => 'system', 'controller' => 'text', 'method' => 'index', 'static' => false, 'args' => array() ) ));
            $item->set('content_access_groups',array(0));
            return $this->edit($item,$type);
		} else {
			return $this->msg(OPAL_Lang::t('ADMIN_WARNING_NEW_CONTENT'), self::STATUS_WARNING);
		}
	}
	
	protected function getFormOptions($item = null){
		$options = parent::getFormOptions($item);
		$options['content_on_site_mode'] = array(OPAL_Lang::t('PAGE_MODE_DONT_SHOW'),OPAL_Lang::t('PAGE_MODE_SHOW_LINE'),OPAL_Lang::t('PAGE_MODE_SHOW_TREE'),OPAL_Lang::t('PAGE_MODE_SHOW_ALWAYS'));
		return $options;
	}

    public function reorderAjax(){
        $updated = OPAM_Page::reorder($root = $this->getPost('root'),$this->getPost('order'),'content_parent_id',$this->user);
        //TODO Clear cache
        return $this->msg(OPAL_Lang::t('ADMIN_CONTENT_REORDERED'), self::STATUS_OK, null, array('IDs' => $updated));
    }
	
}