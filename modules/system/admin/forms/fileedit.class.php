<?php

use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Inputs\Hidden;
use Orange\Forms\Fields\Inputs\Text;
use Orange\Forms\Fields\Inputs\Textarea;
use Orange\Forms\Form;

class OPMX_System_FileEdit extends Form
{

	protected function init($params)
	{
		$this->addField((new Text('file_name', OPAL_Lang::t('file_name')))->requireField());
		if (!empty($params['editable'])) {
			$this->addField((new Textarea('file_data', OPAL_Lang::t('file_data'))));
		}
		$this->addField(new Submit('file_edit_submit', OPAL_Lang::t('ADMIN_SAVE')), 'top');
		$this->addField(new Hidden('file_name_org'));
		$this->enableXSRFProtection();
	}

}