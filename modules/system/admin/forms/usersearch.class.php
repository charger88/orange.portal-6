<?php

class OPMX_System_UserSearch extends OPAL_Form {
		
	protected function build($params){

        $this->addField('order', 'select', OPAL_Lang::t('ADMIN_SORT_BY'), array('options' => array(
            'id'         => 'ID',
            'user_login' => OPAL_Lang::t('user_login'),
            'user_email' => OPAL_Lang::t('user_email'),
            'user_phone' => OPAL_Lang::t('user_phone'),
            'user_name'  => OPAL_Lang::t('user_name'),
        ), 'value'=> 'id', 'required' => true), 'column');

        $this->addField('user_login', 'text', OPAL_Lang::t('user_login'), array(), 'main');
        $this->addField('user_email', 'text', OPAL_Lang::t('user_email'), array(), 'main');
        $this->addField('user_phone', 'text', OPAL_Lang::t('user_phone'), array(), 'main');
        $this->addField('user_name', 'text', OPAL_Lang::t('user_name'), array(), 'main');
        $this->addField('user_group', 'select', OPAL_Lang::t('user_group'), array('options' => $params['groups'], 'value'=> 0, 'required' => true), 'main');
        $this->addField('user_status', 'select', OPAL_Lang::t('ADMIN_STATUS'), array('options' => array(
            -1 => OPAL_Lang::t('ADMIN_DISABLED'),
             1 => OPAL_Lang::t('user_status'),
        )), 'main');

		$this->addField('user_search', 'submit', OPAL_Lang::t('ADMIN_SEARCH'), array('value' => 1), 'buttons');

	}
		
}