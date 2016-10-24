<?php

use \Orange\Forms\Form;
use \Orange\Forms\Components\Fieldset;
use \Orange\Forms\Fields\Selectors\Checkbox;
use \Orange\Forms\Fields\Inputs\Text;
use \Orange\Forms\Fields\Buttons\Submit;

class OPMX_System_UserEdit extends Form {

    protected function init($params){

        $fieldset = new Fieldset('user_groups', OPAL_Lang::t('user_groups'));
        if (!empty($params['options']['user_groups'])) {
            foreach ($params['options']['user_groups'] as $group_id => $group_name) {
                if ($group_id > 0) {
                    $fieldset->addField((new Checkbox('user_groups_' . $group_id, OPAL_Lang::t($group_name)))
                        ->setName('user_groups[]')
                        ->setDefault($group_id));
                }
            }
        }
        $this->addField($fieldset, 'column');

        $this->addField((new Checkbox('user_status', OPAL_Lang::t('user_status')))->setDefault(1), 'column');

        $this->addField((new Text('user_login', OPAL_Lang::t('user_login')))->requireField(), 'main');
        $this->addField((new Text('user_email', OPAL_Lang::t('user_email')))->requireField(), 'main');
        $this->addField((new Text('user_phone', OPAL_Lang::t('user_phone'))), 'main');
        $this->addField((new Text('user_name', OPAL_Lang::t('user_name')))->requireField(), 'main');
        $this->addField((new Text('user_password_new', OPAL_Lang::t('user_password_new')))->requireField()->setAttributes(['autocomplete' => 'off']), 'main');

        $this->addField(new Submit('user_edit_submit', OPAL_Lang::t('ADMIN_SAVE')), 'top');

        $this->enableXSRFProtection();

    }
		
}