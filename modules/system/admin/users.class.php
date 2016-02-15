<?php

class OPMA_System_Users extends OPAL_Controller {

    protected $refs = array();
    protected $content_type = 'content';
    protected $wrapper = 'system/admin-content-wrapper.phtml';
    protected $edit_form_params = array();

    protected $list_columns = array(
        'id'          => array(),
        'user_login'  => array('width' => 15,'link' => '_edit'),
        'user_email'  => array('width' => 15,),
        'user_name'   => array('width' => 15,),
        'user_phone'  => array('width' => 15,),
        'user_groups' => array('width' => 40,),
    );

    public function indexAction(){
        $params = array();
        $params['groups'] = OPAM_User_Group::getRef(true);
        $form = new OPMX_System_UserSearch(OP_WWW.'/'.$this->content->getSlug().'/list','get',$params);
        return $form->getHTML($this->templater,$this->arg('form-prefix','default'));
    }

    public function listAction(){
        $params = array();
        $params['limit']   = $this->arg('limit',50);
        $params['order']   = $this->getGet('order',$this->arg('order','id'));
        $params['desc']    = (bool)$this->getGet('desc',$this->arg('desc',false));
        $params['offset']  = intval($this->getGet('offset',0));
        $params['desc']    = false;
        if ($this->getGet('user_search')){
            $params['filter_login'] = $this->getGet('user_login');
            $params['filter_email'] = $this->getGet('user_email');
            $params['filter_name'] = $this->getGet('user_name');
            $params['filter_phone'] = $this->getGet('user_phone');
            $params['filter_group'] = $this->getGet('user_group');
            $params['filter_status'] = $this->getGet('user_status');
            $params['order'] = $this->getGet('order');
            $params['limit'] = 1000;
            $params['offset'] = null;
        }
        $params['list']    = OPAM_User::getList($params);
        $params['class_fields'] = array('user_status');
        $params['columns'] = $this->list_columns;
        $params['refs'] = $this->getFormOptions();
        $params['columns']['_edit'] = array(
            'title' => '',
            'text'  => OPAL_Lang::t('ADMIN_EDIT'),
            'hint'  => OPAL_Lang::t('ADMIN_EDIT'),
            'class' => 'icon icon-edit',
            'link'  => '/'.$this->content->getSlug().'/edit/%id%',
        );
        return $this->templater->fetch('system/admin-users-list-wrapper.phtml',array(
            'html' => $this->templater->fetch('system/admin-list.phtml',$params),
            'slug' => $this->content->getSlug(),
        ));
    }

    public function newAction(){
        return $this->edit(new OPAM_User());
    }

    public function editAction($id){
        $item = new OPAM_User($id);
        if ($item->id){
            return $this->edit($item);
        } else {
            return $this->msg(OPAL_Lang::t('ADMIN_WARNING_NEW_USER'), self::STATUS_WARNING);
        }
    }

    /**
     * @param OPAM_User $item
     * @return string
     */
    protected function edit($item){
        $params = array();
        $params['options'] = $this->getFormOptions();
        $form = new OPMX_System_UserEdit(OP_WWW.'/'.$this->content->getSlug().'/save/'.$item->id,'post',$params);
        $form->setValues($item->getData(),true);
        return $form->getHTML($this->templater,$this->arg('form-prefix','default'));
    }

    public function saveAction($id = 0){
        $item = new OPAM_User($id);
        $form = new OPMX_System_UserEdit();
        $form->setValues();
        $item->setFromArray($data = $form->getValues());
        $groups = $this->getPost('user_groups');
        $item->set('user_groups',$groups);
        if (!empty($data['user_password_new'])){
            $item->setPassword($data['user_password_new']);
        }
        $item->save();
        $this->log('USER_%s_SAVED', array($item->get('user_login')), 'LOG_CONTENT', self::STATUS_OK, $item);
        return $this->msg(OPAL_Lang::t('ADMIN_SAVED'), self::STATUS_OK, OP_WWW.'/'.$this->content->getSlug().'/edit/'.$item->id);
    }

    protected function getFormOptions(){
        $options = array(
            'user_groups' => OPAM_User_Group::getRef(),
        );
        return $options;
    }

}