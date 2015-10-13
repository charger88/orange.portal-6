<?php

//TODO Commands

class OPMA_System_Content extends OPAL_Controller {

	protected $refs = array();
	protected $content_type = 'content';
	protected $wrapper = 'system/admin-content-wrapper.phtml';
	protected $edit_form_params = array();
	protected $allowed_type_type = 4;
	
	protected $list_columns = array(
		'id'                     => array(),
		'content_type'           => array('width' => 10,),
		'content_title'          => array('width' => 30,'link' => '_edit'),
		'content_slug'           => array('width' => 10,),
		'content_access_groups'  => array('width' => 20,),
		'content_status'         => array('width' => 10,),
		'content_time_published' => array('width' => 10,),
		'content_user_id'        => array('width' => 10,),	
	);
	
	protected $list_class_fields = array('content_status');
	
	public function indexAction(){
		$type = new OPAM_Content_Type('content_type_code',$this->content_type);
		$params = array();
		$params['limit']   = $this->arg('limit',25);
		$params['order']   = $this->getGet('order',$this->arg('order','content_time_published'));
		$params['desc']    = (bool)$this->getGet('desc',$this->arg('desc',true));
		$params['offset']  = intval($this->getGet('offset',0));
		$params['types']   = OPAM_Content_Type::getTypes($this->allowed_type_type,$this->content_type);
		$params['access_level'] = $this->user->get('user_status');
		$params['list']    = OPAM_Content::getList($params,$type->getClass());
		$listMoreData      = OPAM_Content::getListMoreData();
		$params['class_fields'] = $this->list_class_fields;
		if (isset($this->list_columns['content_user_id'])){
			$params['refs']['content_user_id'] = OPAM_User::loadByIDs($listMoreData['content_user_id'],'user_name');
		}
		$params['columns'] = $this->list_columns;
		$params['refs'] = $this->getFormOptions();
		$params['columns']['_edit'] = array(
			'title' => '',
			'text'  => OPAL_Lang::t('ADMIN_EDIT'),
			'hint'  => OPAL_Lang::t('ADMIN_EDIT'),
			'class' => 'icon icon-edit',
			'link'  => '/'.$this->content->getSlug().'/edit/%id%/',
		);
		return $this->wrapContentWithTemplate(
			$this->wrapper,
			$this->templater->fetch('system/admin-list.phtml',$params)
		);
	}
	
	public function newAction($type = ''){
        if (!$type){
            $type = $this->content_type;
        }
		$type = new OPAM_Content_Type('content_type_code',$type);
		$classname = $type->getClass();
        /** @var OPAM_Content $item */
        $item = new $classname();
		$item->set('content_type',$type->get('content_type_code'));
		if ($item->isNewAllowed()){
			return $this->edit($item,$type);
		} else {
			return $this->msg(OPAL_Lang::t('ADMIN_WARNING_NEW_CONTENT'), self::STATUS_WARNING);
		}
	}
	
	public function editAction($id = 0){
		$item = new OPAM_Content($id);
		$type = new OPAM_Content_Type('content_type_code',$item->get('content_type'));
		$classname = $type->getClass();
        /** @var OPAM_Content $item */
        $item = new $classname($id);
		if ($item->isEditable($this->user->get('user_groups'))){
			return $this->edit($item,$type);
		} else {
			return $this->msg(OPAL_Lang::t('ADMIN_WARNING_EDIT_CONTENT'), self::STATUS_WARNING);
		}
	}

    /**
     * @param OPAM_Content $item
     * @param OPAM_Content_Type $type
     * @param bool $validate
     * @return array|null|string
     */
    protected function edit($item,$type,$validate = false){
		if ($this->checkIsTypeAllowed($type)){
			if (!$item->id){
				$item->set('content_status',5);
				$item->set('content_lang',$type->get('content_type_multilang') ? OPAL_Portal::config('system_default_lang','en') : '');
				$item->set('content_type',$type->get('content_type_code'));
			} else {
				$item->set('content_slug',urldecode($item->get('content_slug')));
			}
			$params = $this->edit_form_params;
			$params['options'] = $this->getFormOptions($item);
			$params['type'] = $type;
			$form = new OPMX_System_ContentEdit(OP_WWW.'/'.$this->content->getSlug().'/save/'.$item->id,'post',$params);
			$values = $item->getDataArray();
			if ($texts = $type->get('content_type_texts')){
				foreach ($texts as $text_id => $text_name){
					$textObject = $item->text($text_id);
					$values['content_text_'.$text_id.'_format'] = $textObject->get('content_text_format');
					$values['content_text_'.$text_id] = $textObject->get('content_text_value');
				}
			}
			if ($fields = $type->get('content_type_fields')){
				foreach ($fields as $field_id => $field){
					$values['content_field_'.$field_id] = $item->field($field_id);
				}
			}
			$form->setValues($values,$validate);
			return $form->getHTML($this->templater,$this->arg('form-prefix','default'));
		} else {
			$this->log('CONTENT_TYPE_NOT_ALLOWED_FOR_CONTROLLER', array(), 'LOG_CONTENT', self::STATUS_ERROR);
			return $this->msg(OPAL_Lang::t('CONTENT_TYPE_NOT_ALLOWED_FOR_CONTROLLER'), self::STATUS_ERROR, OP_WWW.'/'.$this->content->getSlug());
		}
	}
	
	public function saveAction($id = 0){
		return $this->save($id);
	}
	
	public function saveAjax($id = 0){
		return $this->save($id);
	}

    /**
     * @param int $id
     * @return array|null|string
     */
    protected function save($id){
		$type = new OPAM_Content_Type('content_type_code',$this->getPost('content_type'));
		$classname = $type->getClass();
        /** @var OPAM_Content $item */
        $item = new $classname($id);
		$cacheids = array();
		if ( ($item->id && $item->isEditable($this->user->get('user_groups'))) || (!$item->id && $item->isNewAllowed()) ){
			if (!$item->id){
				$item->set('content_type',$type->get('content_type_code'));
				$item->set('content_user_id',OPAL_Portal::getInstance()->user->id);
			} else {
				$cacheids[] = $item->get('content_parent_id');
			}
			$type = new OPAM_Content_Type('content_type_code',$item->get('content_type'));
			if ($this->checkIsTypeAllowed($type)){
				$form = new OPMX_System_ContentEdit(OP_WWW.'/'.$this->content->getSlug().'/save/'.$item->id,'post',array(
					'options' => $this->getFormOptions($item),
					'type'    => $type,
				));
				$errors = $form->setValues(null,true);
			
				$values = $form->getValues();
				$item->setFromArray($values);
				$item->set('content_slug',urlencode($item->get('content_slug')));

				if ($fields = $type->get('content_type_fields')){
					foreach ($fields as $field_id => $field){
						$item->setField($field_id,$values['content_field_'.$field_id]);
					}
				}
				
				if (!$errors){

					if ($id = $item->save()){
						if ($texts = $type->get('content_type_texts')){
							foreach ($texts as $text_id => $text_name){
								$textObject = $item->text($text_id);
								$textObject->set('content_text_format',$values['content_text_'.$text_id.'_format']);
								$textObject->set('content_text_value',$values['content_text_'.$text_id]);
								$textObject->save();
							}
						}
				
						$cacheids[] = $item->id;
						$cacheids[] = $item->get('content_parent_id');
						$this->deleteRelatedCache($cacheids);
						$this->log('CONTENT_%s_SAVED', array($item->get('content_title')), 'LOG_CONTENT', self::STATUS_OK, $item);
						return $this->msg(OPAL_Lang::t('ADMIN_SAVED'), self::STATUS_OK, OP_WWW.'/'.$this->content->getSlug().'/edit/'.$item->id);
					} else {
						$this->log('CONTENT_SAVING_FAILED', array(), 'LOG_CONTENT', self::STATUS_ALERT);
						return $this->msg(OPAL_Lang::t('ADMIN_UNEXPECTED_ERROR'), self::STATUS_ALERT, OP_WWW.'/'.$this->content->getSlug());
					}
				} else {
					return $this->edit($item,$type,true);
				}
			} else {
				$this->log('CONTENT_TYPE_NOT_ALLOWED_FOR_CONTROLLER', array(), 'LOG_CONTENT', self::STATUS_ERROR);
				return $this->msg(OPAL_Lang::t('CONTENT_TYPE_NOT_ALLOWED_FOR_CONTROLLER'), self::STATUS_ERROR, OP_WWW.'/'.$this->content->getSlug());
			}
		} else {
			$this->log('CONTENT_NEW_NOT_ALLOWED', array(), 'LOG_CONTENT', self::STATUS_ERROR);
			return $this->msg(OPAL_Lang::t('CONTENT_NEW_NOT_ALLOWED'), self::STATUS_ERROR, OP_WWW.'/'.$this->content->getSlug());
		}
	}

    /**
     * @param OPAM_Content|null $item
     * @return array
     */
    protected function getFormOptions($item = null){
		$type = $item ? new OPAM_Content_Type('content_type_code',$item->get('content_type')) : null;
		if ($type && $type->id){
			if ($type->get('content_type_type') == 3){
				$template_prefix = '';
			} else if ($type->get('content_type_type') == 2){
				$template_prefix = 'block-';
			} else {
				$template_prefix = 'main-';
			}
		} else {
			$template_prefix = $item ? explode('-',$item->get('content_template')) : array();
			$template_prefix = count($template_prefix) > 1 ? $template_prefix[0].'-' : '';
		}
		$options = array(
			'content_template'      => $this->templater->theme->getTemplatesList($template_prefix),
			'content_lang'          => OPAL_Lang::langs(OPAL_Portal::config('system_enabled_langs',array())),
			'content_status'        => array(0 => OPAL_Lang::t('STATUS_REMOVED'),OPAL_Lang::t('STATUS_CANCELED'),OPAL_Lang::t('STATUS_DISABLED'),OPAL_Lang::t('STATUS_DRAFT'),OPAL_Lang::t('STATUS_MODERATION'),OPAL_Lang::t('STATUS_ENABLED'),OPAL_Lang::t('STATUS_APPROVED'),OPAL_Lang::t('STATUS_HOMEPAGE')),
			'content_text_format'   => array(0 => OPAL_Lang::t('CONTENT_TEXT_FORMAT_0'),OPAL_Lang::t('CONTENT_TEXT_FORMAT_1'),OPAL_Lang::t('CONTENT_TEXT_FORMAT_2'),OPAL_Lang::t('CONTENT_TEXT_FORMAT_3'),OPAL_Lang::t('CONTENT_TEXT_FORMAT_4')),
			'content_access_groups' => OPAM_User_Group::getRef(),
			'content_area'          => array_merge($this->templater->theme->getThemeAreas(),$this->templater->theme->getAdminAreas()),
		);
		foreach ($options['content_access_groups'] as $key => $value){
			$options['content_access_groups'][$key] = OPAL_Lang::t($value);
		}
		if ($item){
			$controllerReflection = new ReflectionClass($item);
			try {
				$options['content_parent_id'] = $controllerReflection->getMethod('getParentsRef')->invokeArgs($item, array());
			} catch (ReflectionException $e){
				$options['content_parent_id'] = null;
			}
			try {
				$options['content_default_lang_id'] = $controllerReflection->getMethod('getDefaultLanguageRef')->invokeArgs($item, array( OPAL_Portal::config('system_default_lang') ));
			} catch (ReflectionException $e){
				$options['content_default_lang_id'] = null;
			}
			$options['content_area'] = (strpos($item->get('content_slug'),'admin/') === 0 ? $this->templater->theme->getAdminAreas() : $this->templater->theme->getThemeAreas());
			foreach ($options as $key => &$ref){
				if ($key != 'content_text_format'){
					$value = $item->get($key);
					$value = is_array($value) ? $value : array($value);
					foreach ($value as $val){
						if (!isset($ref[$val])){
							if (!empty($val)){
								$ref[$val] = $val;
							}
						}
					}
				}
			}
		}
		return $options;
	}
	
	protected function deleteRelatedCache($ids){
		if ($ids = array_unique($ids)){
			foreach ($ids as $id){
				$this->deleteMethodCache('OPMC_System_Menu','indexAction',$id);
				$this->deleteMethodCache('OPMC_System_Menu','indexAjax',$id);
				$this->deleteMethodCache('OPMC_System_Menu','indexBlock',$id);
				$this->deleteMethodCache('OPMC_System_Menu','treeAction',$id);
				$this->deleteMethodCache('OPMC_System_Menu','treeAjax',$id);
				$this->deleteMethodCache('OPMC_System_Menu','treeBlock',$id);
			}
		}
	}

    /**
     * @param OPAM_Content_Type $type
     * @return bool
     */
    protected function checkIsTypeAllowed($type){
		return (
			($type->get('content_type_type') == $this->allowed_type_type) 
			&& (
				($this->allowed_type_type == 1)
				||
				($this->allowed_type_type == 2)
				||
				($this->allowed_type_type == 4)
				||
				($this->content_type == $type->get('content_type_code'))
			)	
		);
	}
	
}

