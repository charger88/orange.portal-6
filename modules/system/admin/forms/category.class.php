<?php

class OPMX_System_Category extends OPAL_Form {
		
	protected function build($params){

		$this->addField('category', 'text', OPAL_Lang::t('ADMIN_CATEGORY_PAGE'), array());
		
		$this->addField('category_submit', 'submit', OPAL_Lang::t('ADMIN_ADD'), array());
		
	}
	
	
}