<?php

use \Orange\Forms\Form;
use \Orange\Forms\Fields\Inputs\Text;
use \Orange\Forms\Fields\Buttons\Submit;

class OPMX_System_Category extends Form {

    protected function init($params){
        $this->addField(new Text('category', OPAL_Lang::t('ADMIN_CATEGORY_PAGE')));
        $this->addField(new Submit('category_submit', OPAL_Lang::t('ADMIN_ADD')), 'top');
        $this->enableXSRFProtection();
    }

}