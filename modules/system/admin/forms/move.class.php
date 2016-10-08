<?php

use \Orange\Forms\Form;
use \Orange\Forms\Fields\Html;
use \Orange\Forms\Fields\Inputs\Text;
use \Orange\Forms\Fields\Buttons\Submit;

class OPMX_System_Move extends Form {

    protected function init($params){
        $this->addField(new Text('system_domain', OPAL_Lang::t('ADMIN_URL_DOMAIN')));
        $this->addField(new Text('system_base_dir', OPAL_Lang::t('ADMIN_URL_PATH')));
        $this->addField(new Html('<p>' . OPAL_Lang::t('ADMIN_URL_ACTION_INFO') . '</p>'));
        $this->addField(new Submit('content_edit_submit', OPAL_Lang::t('ADMIN_MOVE')), 'top');
    }
	
}