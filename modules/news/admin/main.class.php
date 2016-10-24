<?php

class OPMA_News_Main extends OPMA_System_Content {

    protected $content_type = 'news_item';
    protected $wrapper = 'news/admin-news-wrapper.phtml';
    protected $allowed_type_type = 3;

    protected $list_columns = array(
        'content_title'          => array('width' => 70,'link' => '_edit'),
        'content_user_id'        => array('width' => 20,),
        'content_status'         => array('width' => 10,),
    );

    public function newAction($type = null){
        $type = new OPAM_Content_Type('content_type_code',$this->content_type);
        $item = new OPMM_News_Item();
        $item->set('content_type',$type->get('content_type_code'));
        if ($item->isNewAllowed()){
            $item->set('content_time_published',time());
            $item->set('content_template','main-html.phtml');
            $item->set('content_commands',array( array( 'module' => 'news', 'controller' => 'main', 'method' => 'view', 'static' => false, 'args' => array() ) ));
            $item->set('content_access_groups',array(0));
            return $this->edit($item,$type);
        } else {
            return $this->msg(OPAL_Lang::t('ADMIN_WARNING_NEW_CONTENT'), self::STATUS_WARNING);
        }
    }

    protected function edit($item,$type, $validate = false){
        $this->edit_form_params['lang_overwrite'] = array('content_parent_id' => OPAL_Lang::t('ADMIN_CATEGORY'));
        return parent::edit($item, $type, $validate);
    }

    protected function getFormOptions($item = null){
        $options = parent::getFormOptions($item);
        if ($IDs = OPAL_Portal::getInstance()->config('news_categories',array())) {
            $pages = OPAM_Page::getList(array(
                'types' => OPAM_Content_Type::getPageTypes(),
                'IDs' => $IDs,
                'order' => 'content_title',
                'status_min' => OPAM_Content::STATUS_DRAFT,
            ), 'OPAM_Page');
            $categories = array();
            foreach ($pages as $page){
                if ($page->isReadable($this->user->get('user_groups'))) {
                    $categories[$page->id] = $page->get('content_title');
                }
            }
            if ($item && $item->get('content_parent_id') && (!isset($categories[$item->get('content_parent_id')]))){
                $categories[$item] = OPAL_Lang::t('ADMIN_UNKNOWN_CATEGORY');
            }
            $options['content_parent_id'] = $categories;
        }
        return $options;
    }

    public function categoriesAction(){
        $pages = OPAM_Page::getList(
            [
                'types' => OPAM_Content_Type::getPageTypes(),
                'access_user' => $this->user,
                'order' => 'content_title',
                'status_min' => OPAM_Content::STATUS_DRAFT,
            ], 'OPAM_Page'
        );
        $selector_pages = array_map(function($page) {
            return $page->get('content_title');
        }, $pages);
        if ($IDs = OPAL_Portal::getInstance()->config('news_categories', [])) {
            foreach ($IDs as $id) {
                unset($selector_pages[$id]);
            }
        }
        return $this->templater->fetch('system/admin-categories.phtml', [
            'IDs' => $IDs,
            'pages' => $pages,
            'add_form' => (new OPMX_System_Category(['selector_pages' => $selector_pages]))->setAction(OP_WWW.'/admin/news/addcategory'),
            'url_base' => $this->content->getURL(),
            'selector_pages' => $selector_pages,
        ]);
    }

    public function addcategoryAction(){
        $values = (new OPMX_System_Category())->setValues($this->getPostArray())->getValuesWithXSRFCheck();
        $IDs = OPAL_Portal::getInstance()->config('news_categories',[]);
        if ($id = intval($values['category'])){
            $IDs[] = $id;
        }
        $option = new OPAM_Config('config_key','news_categories');
        if (!$option->id){
            $option->set('config_type','LIST');
            $option->set('config_key','news_categories');
        }
        $option->set('config_value',array_unique($IDs));
        $option->save();
        return $this->msg(OPAL_Lang::t('ADMIN_CATEGORY_ADDED'), self::STATUS_OK, $this->content->getURL().'/categories');
    }

    public function deletecategoryAction($id){
        $IDs = OPAL_Portal::getInstance()->config('news_categories',[]);
        foreach ($IDs as $i => $cid){
            if ($cid == $id){
                unset($IDs[$i]);
            }
        }
        $option = new OPAM_Config('config_key','news_categories');
        $option->set('config_value',$IDs);
        $option->save();
        return $this->msg(OPAL_Lang::t('ADMIN_CATEGORY_DELETED'), self::STATUS_OK, $this->content->getURL().'/categories');
    }

}