<?php

class OPMX_System_ContentEdit extends OPAL_Form {
	
	private $lang_overwrite = array();

    /**
     * @param array $params
     */
    protected function build($params){

        /** @var OPAM_Content_Type $type */
        $type = $params['type'];
		
		$this->lang_overwrite = isset($params['lang_overwrite']) ? $params['lang_overwrite'] : array();

		$params['hide'] = $type->get('content_type_hidden');
		$params['options'] = isset($params['options']) ? $params['options'] : array();

		/* Top BEGIN */
		
		$this->addField('content_title', 'text', $this->lng('content_title'), array('class' => 'input-title'), 'top');
		
		/* Top EMD */
		
		/* Column BEGIN */

		if (!in_array('content_lang', $params['hide']) && (count($params['options']['content_lang']) > 1)){
			$this->addFieldset($this->lng('LANG_OPTIONS'), 'column', 'fieldset-lang');
			$this->addField('content_lang', 'select', $this->lng('content_lang'), array('options' => isset($params['options']['content_lang']) ? $params['options']['content_lang'] : array()), 'column');
			$this->addField('content_default_lang_id', 'select', $this->lng('content_default_lang_id'), array('options' => isset($params['options']['content_default_lang_id']) ? $params['options']['content_default_lang_id'] : array()), 'column');
			$this->addFieldset(null, 'column');
		}
		
		if (!in_array('content_status', $params['hide']) || !in_array('content_time_published', $params['hide'])){
				
			$this->addFieldset($this->lng('PUBLISHING'), 'column', 'fieldset-publishing');
				
			if (!in_array('content_status', $params['hide']) && !empty($params['options']['content_status'])){
				$this->addField('content_status', 'select', $this->lng('content_status'), array('required' => 'required', 'options' => isset($params['options']['content_status']) ? $params['options']['content_status'] : array()), 'column');
			}
				
			if (!in_array('content_time_published', $params['hide'])){
				$this->addField('content_time_published', 'datetime', $this->lng('content_time_published'), array(), 'column');
			}
				
			$this->addFieldset(null, 'column');
				
		}
		
		if (!in_array('content_access_groups', $params['hide'])){
			$this->addFieldset($this->lng('ACCESS'), 'column', 'access');
			if (!empty($params['options']['content_access_groups'])){
				foreach ($params['options']['content_access_groups'] as $group_id => $group_name){
					$this->addField('content_access_groups_'.$group_id, 'checkbox', $this->lng($group_name), array('name' => 'content_access_groups[]','value' => $group_id), 'column');
				}
			}
			$this->addFieldset(null, 'column');
		}
		
		if (!in_array('content_parent_id', $params['hide'])){
			$this->addFieldset($this->lng('STRUCTURE'), 'column', 'structure');
			$this->addField('content_parent_id', 'select', $this->lng('content_parent_id'), array('options' => isset($params['options']['content_parent_id']) ? $params['options']['content_parent_id'] : array()), 'column');
			$this->addFieldset(null, 'column');
		}
		
		if (
			!in_array('content_area', $params['hide']) || 
			!in_array('content_on_site_mode', $params['hide']) || 
			!in_array('content_image', $params['hide']) || 
			!in_array('content_template', $params['hide'])
		){
		
			$this->addFieldset($this->lng('VIEW'), 'column', 'view');
			
			if (!in_array('content_area', $params['hide'])){
				$this->addField('content_area', 'select', $this->lng('content_area'), array('options' => isset($params['options']['content_area']) ? $params['options']['content_area'] : array()), 'column');
			}
			
			if (!in_array('content_on_site_mode', $params['hide']) && !empty($params['options']['content_on_site_mode'])){
				$this->addField('content_on_site_mode', 'select', $this->lng('content_on_site_mode'), array('required' => 'required', 'options' => isset($params['options']['content_on_site_mode']) ? $params['options']['content_on_site_mode'] : array()), 'column');
			}
			
			if (!in_array('content_image', $params['hide'])){
				$this->addField('content_image', 'text', $this->lng('content_image'), array('class' => 'op-media'), 'column');
			}
			
			if (!in_array('content_template', $params['hide'])){
				$fp = array('required' => 'required', 'options' => isset($params['options']['content_template']) ? $params['options']['content_template'] : array());
				if ($type->get('content_type_type') == 1){
					$fp['required'] = 'required';
				}
				$this->addField('content_template', 'select', $this->lng('content_template'), $fp, 'column');
			}
			
			$this->addFieldset(null, 'column');
		
		}
		
		if ($ctfields = $type->get('content_type_fields')){
			$last_field_group = '';
			foreach ($ctfields as $field_id => $field){
				if ($last_field_group != $field['group']){
					if ($last_field_group){
						$this->addFieldset(null, 'column');
					}
					$this->addFieldset($this->lng($field['group']), 'column', 'fieldset-'.md5($field['group']));
				}
				switch ($field['type']){
					case 'BOOLEAN':
						$field_type = 'checkbox';
					break;
					case 'NLLIST':
						$field_type = 'textarea';
					break;
					default:
						$field_type = 'text';
					break;
				}
				$field_params = $field_type == 'checkbox' ? array('value' => 1) : array();
				$this->addField('content_field_'.$field_id, $field_type, $this->lng($field['title']), $field_params, 'column');
				$last_field_group = $field['group'];
			}
			if ($last_field_group){
				$this->addFieldset(null, 'column');
			}
		}
		
		/* Column END */
		
		/* Main BEGIN */
		
		if (!in_array('content_slug', $params['hide'])){
            if (!empty($params['options']['content_template'])){
                $field_options = array_keys($params['options']['content_template']);
                $field_options = strpos($field_options[0],'block-') === 0 ? array() : array('data-postfix' => '.html');
            } else {
                $field_options = array();
            }
			$this->addField('content_slug', 'text', $this->lng('content_slug'), $field_options, 'main');
		}
		
		if ($texts = $type->get('content_type_texts')){
			foreach ($texts as $text_id => $text_name){
				$this->addField('content_text_'.$text_id, 'wysiwyg', $this->lng($text_name), $text_id == 'text' ? array('class' => 'full-text') : array(), 'main');
				$this->addField('content_text_'.$text_id.'_format', 'select', $this->lng('content_text_format'), array('required' => 'required', 'options' => isset($params['options']['content_text_format']) ? $params['options']['content_text_format'] : array()), 'main');
			}
		}
		
		if (!in_array('content_commands', $params['hide'])){
			
			$this->addField('content_commands:module', 'text', OPAL_Lang::t('content_commands:module'), array(), 'content_commands[]');
			$this->addField('content_commands:controller', 'text', OPAL_Lang::t('content_commands:controller'), array(), 'content_commands[]');
			$this->addField('content_commands:method', 'text', OPAL_Lang::t('content_commands:method'), array(), 'content_commands[]');
			$this->addField('content_commands:static', 'text', OPAL_Lang::t('content_commands:static'), array(), 'content_commands[]');
			$this->addField('content_commands:args', 'text', OPAL_Lang::t('content_commands:args'), array(), 'content_commands[]');
			$this->addMultirow('content_commands', 'main');
			
		}

		/* Main END */
		
		/* Buttons BEGIN */
		
		$this->addField('content_type', 'hidden');
		
		$this->addField('content_edit_submit', 'submit', OPAL_Lang::t('ADMIN_SAVE'), array(), 'buttons');
		
		/* Buttons END */
		
		$this->addAfter(array('template','system/admin-media-init.phtml',array()));

        OPAL_Theme::addScriptFile('modules/system/static/js/admin-content-form.js');

	}
	
	public function setValues($values = null,$validate = false){
		$return = parent::setValues($values,$validate);	
		if ($this->fields['content_commands']['value']){
			foreach ($this->fields['content_commands']['value'] as $n => $fieldData){
				if (isset($fieldData['args']) && is_array($fieldData['args'])){
					$this->fields['content_commands']['value'][$n]['args'] = json_encode($fieldData['args']);
				}
			}
		}
		return $return;
	}
	
	public function getValues(){
		$return = parent::getValues();
		if ($return['content_commands']){
			foreach ($return['content_commands'] as $n => $fieldData){
				if (!is_array($fieldData['args'])){
					$return['content_commands'][$n]['args'] = $fieldData['args'] ? json_decode($fieldData['args'],true) : array();
				}
			}
		}
		return $return;
	}
	
	
	private function lng($key){
		return OPAL_Lang::t(isset($this->lang_overwrite[$key]) ? $this->lang_overwrite[$key] : $key);
	}
	
}