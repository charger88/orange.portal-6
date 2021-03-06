<?php

use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Html;
use Orange\Forms\Fields\Inputs\Hidden;
use Orange\Forms\Fields\Inputs\Password;
use Orange\Forms\Fields\Inputs\Text;
use Orange\Forms\Form;

class OPMF_System_Signin extends Form
{

	protected function init($params)
	{

		$this->addField((new Text('signin_login', \Orange\Portal\Core\App\Lang::t('Username'))));
		$this->addField((new Password('signin_password', \Orange\Portal\Core\App\Lang::t('Password'))));
		$this->addField((new Hidden('signin_redirect')));
		$this->addField((new Submit('signin_submit', \Orange\Portal\Core\App\Lang::t('Sign In'))));

		if (isset($params['recovery'])) {
			$this->addField(new Html($params['recovery']));
		}
		if (isset($params['registration'])) {
			$this->addField(new Html($params['registration']));
		}
		if (!is_null(\Orange\Portal\Core\Model\User::$auth_error)) {
			$this->addError('signin_submit', \Orange\Portal\Core\Model\User::$auth_error);
		}

	}

}