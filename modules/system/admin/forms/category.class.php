<?php

use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Selectors\Select;
use Orange\Forms\Form;

class OPMX_System_Category extends Form
{

	protected function init($params)
	{
		$this->addField((new Select('category', \Orange\Portal\Core\App\Lang::t('ADMIN_CATEGORY_PAGE')))->setEmptyOption(Select::EMPTY_OPTION_ALWAYS)->setOptions(isset($params['selector_pages']) ? $params['selector_pages'] : []));
		$this->addField((new Submit('category_submit', \Orange\Portal\Core\App\Lang::t('ADMIN_ADD'))), 'top');
		$this->enableXSRFProtection();
	}

}