<?php

use \Orange\Forms\Form;
use \Orange\Forms\Fields\Inputs\Search;
use \Orange\Forms\Fields\Buttons\Button;

class OPMF_System_Search extends Form {
		
	protected function init($params){
        $this->addField((new Search('search', OPAL_Lang::t('SEARCH')))->placeholder());
        $this->addField((new Button('signin_submit', OPAL_Lang::t('FIND'))));
    }
	
}