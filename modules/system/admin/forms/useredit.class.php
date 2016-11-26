<?php

use Orange\Forms\Components\Fieldset;
use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Inputs\Text;
use Orange\Forms\Fields\Selectors\Checkbox;
use Orange\Forms\Form;

class OPMX_System_UserEdit extends Form
{

	protected function init($params)
	{

		$fieldset = new Fieldset('user_groups', \Orange\Portal\Core\App\Lang::t('user_groups'));
		if (!empty($params['options']['user_groups'])) {
			foreach ($params['options']['user_groups'] as $group_id => $group_name) {
				if ($group_id > 0) {
					$fieldset->addField((new Checkbox('user_groups_' . $group_id, \Orange\Portal\Core\App\Lang::t($group_name)))
						->setName('user_groups[]')
						->setDefault($group_id));
				}
			}
		}
		$this->addField($fieldset, 'column');

		$this->addField((new Checkbox('user_status', \Orange\Portal\Core\App\Lang::t('user_status')))->setDefault(1), 'column');

		$this->addField((new Text('user_login', \Orange\Portal\Core\App\Lang::t('user_login')))->requireField(), 'main');
		$this->addField((new Text('user_email', \Orange\Portal\Core\App\Lang::t('user_email')))->requireField(), 'main');
		$this->addField((new Text('user_phone', \Orange\Portal\Core\App\Lang::t('user_phone'))), 'main');
		$this->addField((new Text('user_name', \Orange\Portal\Core\App\Lang::t('user_name')))->requireField(), 'main');
		$this->addField((new Text('user_password_new', \Orange\Portal\Core\App\Lang::t('user_password_new')))->requireField()->setAttributes(['autocomplete' => 'off']), 'main');

		$this->addField(new Submit('user_edit_submit', \Orange\Portal\Core\App\Lang::t('ADMIN_SAVE')), 'top');

		$this->enableXSRFProtection();

	}

}