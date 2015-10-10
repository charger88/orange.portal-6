<?php

class OPMA_System_Groups extends OPAL_Controller {
	
	protected $list_columns = array(
		'id'                   => array(),
		'group_name'        => array('width' => 20,'link' => '_edit'),
        'group_module'      => array('width' => 15,),
		'group_description' => array('width' => 65,),
	);

	public function indexAction(){
		$params = array();
		$params['list'] = OPAM_User_Group::getList();
		$params['columns'] = $this->list_columns;
		$params['columns']['_users'] = array(
			'title' => '',
			'text'  => OPAL_Lang::t('ADMIN_USERS'),
			'hint'  => OPAL_Lang::t('ADMIN_USERS'),
			'class' => 'icon icon-users',
			'link'  => '/'.$this->content->getSlug().'/users/%id%',
		);
        $params['columns']['_edit'] = array(
            'title' => '',
            'text'  => OPAL_Lang::t('ADMIN_EDIT'),
            'hint'  => OPAL_Lang::t('ADMIN_EDIT'),
            'class' => 'icon icon-edit',
            'link'  => '/'.$this->content->getSlug().'/edit/%id%',
        );
		return $this->templater->fetch('system/admin-types-list-wrapper.phtml',array(
			'html' => $this->templater->fetch('system/admin-list.phtml',$params),
			'slug' => $this->content->getSlug(),
		));
	}
	
	public function newAction(){
		return $this->edit(new OPAM_User_Group());
	}
	
	public function editAction($id){
		$item = new OPAM_User_Group($id);
		if ($item->id){
			return $this->edit($item);
		} else {
			return $this->msg(OPAL_Lang::t('ADMIN_WARNING_NEW_GROUP'), self::STATUS_WARNING);
		}
	}

    /**
     * @param OPAM_User_Group $item
     * @return string
     */
    protected function edit($item){
		$form = new OPMX_System_GroupEdit(OP_WWW.'/'.$this->content->getSlug().'/save/'.$item->id,'post');
		$form->setValues($item->getDataArray(),true);
		return $form->getHTML($this->templater,$this->arg('form-prefix','default'));
	}
	
	public function saveAction($id = 0){
        $item = new OPAM_User_Group($id);
		$form = new OPMX_System_GroupEdit();
		$form->setValues();
		$item->setFromArray($form->getValues());
		$item->save();
		$this->log('GROUP_%s_SAVED', array($item->get('group_name')), 'LOG_USERS', self::STATUS_OK, $item);
		return $this->msg(OPAL_Lang::t('ADMIN_SAVED'), self::STATUS_OK, OP_WWW.'/'.$this->content->getSlug().'/edit/'.$item->id);
	}
	
}