<?php

class OPMF_System_Search extends OPAL_Form {
		
	protected function build($params){
		$this->addField('search', 'text', null, array('placeholder' => OPAL_Lang::t('SEARCH')));
		$this->addField(null, 'submit', OPAL_Lang::t('FIND'));
	}
	
}