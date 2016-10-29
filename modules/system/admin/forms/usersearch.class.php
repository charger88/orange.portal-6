<?php

use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Inputs\Text;
use Orange\Forms\Fields\Selectors\Select;
use Orange\Forms\Form;

class OPMX_System_UserSearch extends Form
{

	protected function init($params)
	{

		$this->addField((new Select('order', OPAL_Lang::t('ADMIN_SORT_BY'), [
			'id' => 'ID',
			'user_login' => OPAL_Lang::t('user_login'),
			'user_email' => OPAL_Lang::t('user_email'),
			'user_phone' => OPAL_Lang::t('user_phone'),
			'user_name' => OPAL_Lang::t('user_name'),
		]))->requireField()->setDefault('user_login'), 'column');

		$this->addField((new Text('user_login', OPAL_Lang::t('user_login'))), 'main');
		$this->addField((new Text('user_email', OPAL_Lang::t('user_email'))), 'main');
		$this->addField((new Text('user_phone', OPAL_Lang::t('user_phone'))), 'main');
		$this->addField((new Text('user_name', OPAL_Lang::t('user_name'))), 'main');

		$this->addField((new Select('user_group', OPAL_Lang::t('user_group'), $params['groups']))->requireField()->setDefault(0), 'main');

		$this->addField((new Select('user_group', OPAL_Lang::t('ADMIN_STATUS'), [
			-1 => OPAL_Lang::t('ADMIN_DISABLED'),
			1 => OPAL_Lang::t('user_status'),
		]))->requireField(), 'main');

		$this->addField((new Submit('user_search', OPAL_Lang::t('ADMIN_SEARCH')))
			->setDefault(1), 'top');

	}

}