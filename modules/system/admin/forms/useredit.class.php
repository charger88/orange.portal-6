<?php

class OPMX_System_UserEdit extends OPAL_Form {
		
	protected function build($params){

        $this->addFieldset(OPAL_Lang::t('user_groups'), 'column', 'groups');
        if (!empty($params['options']['user_groups'])){
            foreach ($params['options']['user_groups'] as $group_id => $group_name){
                if ($group_id > 0){
                    $this->addField('user_groups_'.$group_id, 'checkbox', OPAL_Lang::t($group_name), array('name' => 'user_groups[]','value' => $group_id), 'column');
                }
            }
        }
        $this->addFieldset(null, 'column');
        $this->addField('user_status', 'checkbox', OPAL_Lang::t('user_status'), array('value' => 1), 'column');

        $this->addField('user_login', 'text', OPAL_Lang::t('user_login'), array('required' => 'required',), 'main');
        $this->addField('user_email', 'email', OPAL_Lang::t('user_email'), array('required' => 'required',), 'main');
        $this->addField('user_phone', 'text', OPAL_Lang::t('user_phone'), array(), 'main');
        $this->addField('user_name', 'text', OPAL_Lang::t('user_name'), array(), 'main');
        $this->addField('user_password_new', 'text', OPAL_Lang::t('user_password_new'), array('autocomplete' => 'off'), 'main');

		$this->addField('user_edit_submit', 'submit', OPAL_Lang::t('ADMIN_SAVE'), array(), 'buttons');

	}
		
}