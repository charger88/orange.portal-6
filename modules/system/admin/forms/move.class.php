<?php

use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Html;
use Orange\Forms\Fields\Inputs\Text;
use Orange\Forms\Form;

class OPMX_System_Move extends Form
{

	protected function init($params)
	{
		$this->addField(new Text('system_domain', \Orange\Portal\Core\App\Lang::t('ADMIN_URL_DOMAIN')));
		$this->addField(new Text('system_base_dir', \Orange\Portal\Core\App\Lang::t('ADMIN_URL_PATH')));
		$this->addField(new Html('<p>' . \Orange\Portal\Core\App\Lang::t('ADMIN_URL_ACTION_INFO') . '</p>'));
		$this->addField(new Submit('content_edit_submit', \Orange\Portal\Core\App\Lang::t('ADMIN_MOVE')), 'top');
		$this->enableXSRFProtection();
	}

}