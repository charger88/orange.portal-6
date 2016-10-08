<?php

use \Orange\Forms\Form;
use \Orange\Forms\Fields\Inputs\Text;
use \Orange\Forms\Fields\Buttons\Submit;

class OPMX_System_GroupEdit extends Form {

    protected function init($params){
        $this->addField((new Text('group_name', OPAL_Lang::t('group_name')))->requireField());
        $this->addField((new Text('group_description', OPAL_Lang::t('group_description'))));
        $this->addField(new Submit('type_edit_submit', OPAL_Lang::t('ADMIN_SAVE')), 'top');
    }
    
}