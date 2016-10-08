<?php

use \Orange\Forms\Form;
use \Orange\Forms\Fields\Inputs\Text;
use \Orange\Forms\Fields\Inputs\Password;
use \Orange\Forms\Fields\Inputs\Hidden;
use \Orange\Forms\Fields\Buttons\Button;
use \Orange\Forms\Fields\Html;

class OPMF_System_Signin extends Form {
	
	public static $error = null;
	
	protected function init($params){

        $this->addField((new Text('signin_login', OPAL_Lang::t('Username'))));
        $this->addField((new Password('signin_password', OPAL_Lang::t('Password'))));
        $this->addField((new Hidden('signin_redirect')));
        $this->addField((new Button('signin_submit', OPAL_Lang::t('Sign In'))));

		if (isset($params['recovery'])){
			$this->addField(new Html($params['recovery']));
		}
		if (isset($params['registration'])){
            $this->addField(new Html($params['registration']));
		}
		if (!is_null(self::$error)){
			$this->addError('signin_submit', self::$error);
		}

	}
	
}