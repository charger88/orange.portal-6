<?php

class OPMF_System_Signin extends OPAL_Form {
	
	public static $error = null;
	
	protected function build($params){
		$this->addField('signin_login', 'text', OPAL_Lang::t('Username'));
		$this->addField('signin_password', 'password', OPAL_Lang::t('Password'));
		$this->addField('signin_redirect', 'hidden');
		$this->addField('signin_submit', 'submit', OPAL_Lang::t('Sign In'));
		if (isset($params['recovery'])){
			$this->addHTML($params['recovery']);
		}
		if (isset($params['registration'])){
			$this->addHTML($params['registration']);
		}
		if (!is_null(self::$error)){
			$this->setError('signin_submit', self::$error);
		}
	}
	
}