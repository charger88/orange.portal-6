<?php

use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Inputs\Search;
use Orange\Forms\Form;

class OPMF_System_Search extends Form
{

	protected function init($params)
	{
		$this->addField((new Search('search', OPAL_Lang::t('SEARCH')))->placeholder()->requireField());
		$this->addField((new Submit('signin_submit', OPAL_Lang::t('FIND'))));
	}

}