<?php

class OPMX_System_GroupEdit extends OPAL_Form {
		
	protected function build($params){
		
		$this->addField('group_name', 'text', OPAL_Lang::t('group_name'), array('required' => 'required'));
		$this->addField('group_description', 'text', OPAL_Lang::t('group_description'), array());

		$this->addField('type_edit_submit', 'submit', OPAL_Lang::t('ADMIN_SAVE'), array(), 'buttons');
		
	}
		
}