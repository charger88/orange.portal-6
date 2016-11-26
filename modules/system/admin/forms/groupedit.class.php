<?php

use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Inputs\Text;
use Orange\Forms\Form;

class OPMX_System_GroupEdit extends Form
{

	protected function init($params)
	{
		$this->addField((new Text('group_name', \Orange\Portal\Core\App\Lang::t('group_name')))->requireField());
		$this->addField((new Text('group_description', \Orange\Portal\Core\App\Lang::t('group_description'))));
		$this->addField(new Submit('group_edit_submit', \Orange\Portal\Core\App\Lang::t('ADMIN_SAVE')), 'top');
		$this->enableXSRFProtection();
	}

}