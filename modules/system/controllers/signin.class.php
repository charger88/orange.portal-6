<?php

class OPMC_System_Signin extends OPAL_Controller {
	
	public function indexAction(){
		if (!($redirect = $this->getPost('signup_redirect'))){
			$redirect = $this->arg('error', false) ? $this->getServer('REQUEST_URI') : $this->getHTTPReferer();
		}
		return $this->index($redirect);
	}
	
	public function indexAjax(){
		if (!($redirect = $this->getPost('signup_redirect'))){
			$redirect = $this->getHTTPReferer();
		}
		return $this->msg($this->index($redirect),self::STATUS_OK);
	}
	
	public function indexBlock(){
		if (!($redirect = $this->getPost('signup_redirect'))){
			$redirect = $this->getServer('REQUEST_URI');
		}
		return $this->index($redirect);
	}
	
	private function index($redirect){
		if ($this->user->id){
			return $this->templater->fetch('system/'.$this->arg('prefix','default').'-authorized.phtml');
		} else {
			$form = new OPMF_System_Signin();
			$form->setValues(array('signup_redirect' => $redirect));
			return $form->getHTML();
		}
	}
	
	public function goHook(){
		$return = false;
		$signin_login = $this->getPost('signin_login');
		$signin_password = $this->getPost('signin_password');
		if (!is_null($signin_login) && !is_null($signin_password)){
			$user = new OPAM_User('user_login',$signin_login);
			if ($user->id){
				if ($user->verifyPassword($signin_password)){
					if ($user->get('user_status') > 0){
						OPAL_Portal::getInstance()->user = $user;
						$return = true;
					} else {
						OPMF_System_Signin::$error = OPAL_Lang::t('Account was blocked.');
					}
				} else {
					OPMF_System_Signin::$error = OPAL_Lang::t('Password is wrong.');
				}
			} else {
				OPMF_System_Signin::$error = OPAL_Lang::t('Account was not found.');
			}
		} else {
			$return = null;
		}
		return $return;
	}

    public function offActionDirect(){
        OPAL_Portal::getInstance()->session->set('uid',null);
        return $this->redirect(OP_WWW);
    }
	
}