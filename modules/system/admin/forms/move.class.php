<?php

class OPMX_System_Move extends OPAL_Form {
		
	protected function build($params){

		$this->addField('system_domain', 'text', OPAL_Lang::t('ADMIN_URL_DOMAIN'), array());
		$this->addField('system_base_dir', 'text', OPAL_Lang::t('ADMIN_URL_PATH'), array());
		
		$this->addHTML('<p>'.OPAL_Lang::t('ADMIN_URL_ACTION_INFO').'</p>');
		
		$this->addField('content_edit_submit', 'submit', OPAL_Lang::t('ADMIN_MOVE'), array(), 'buttons');
		
	}
	
	
}