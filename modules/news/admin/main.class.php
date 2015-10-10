<?php

class OPMA_News_Main extends OPMA_System_Content {

    protected $content_type = 'news_item';
    protected $wrapper = 'news/admin-news-wrapper.phtml';
    protected $allowed_type_type = 3;

    protected $list_columns = array(
        'content_title'          => array('width' => 90,'link' => '_edit'),
        'content_status'         => array('width' => 10,),
    );

    public function newAction($type = null){
        $type = new OPAM_Content_Type('content_type_code',$this->content_type);
        $item = new OPMM_News_Item();
        $item->set('content_type',$type->get('content_type_code'));
        if ($item->isNewAllowed()){
            $item->set('content_time_published',OPDB_Functions::getTime());
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
        return $options;
    }

}