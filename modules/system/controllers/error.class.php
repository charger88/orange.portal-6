<?php

class OPMC_System_Error extends OPAL_Controller {
	
	public function notfoundAction(){
		$controller = new OPMC_System_Text($this->content, $this->user, $this->session, $this->templater);
		return $controller->indexAction();
	}

	public function notfoundAjax(){
		$controller = new OPMC_System_Text($this->content, $this->user, $this->session, $this->templater);
		return $this->msg(OPAL_Lang::t('ERROR_PAGE_NOT_FOUND'), self::STATUS_NOTFOUND, null, [
			'text' => $controller->indexAjax()
		]);
	}

	public function notfoundCli(){
		return OPAL_Lang::t('ERROR_PAGE_NOT_FOUND');
	}
	
	public function unauthorizedAction(){
		$controller = new OPMC_System_Signin($this->content, $this->user, $this->session, $this->templater, ['error' => true]);
		return $controller->indexAction();
	}
	
	public function unauthorizedAjax(){
		$controller = new OPMC_System_Signin($this->content, $this->user, $this->session, $this->templater, ['error' => true]);
		return $this->msg(OPAL_Lang::t('ERROR_PAGE_UNAUTHORIZED'), self::STATUS_NOTFOUND, null, [
			'text' => $controller->indexAjax()
		]);
	}
	
	public function unauthorizedCli(){
		return OPAL_Lang::t('ERROR_PAGE_UNAUTHORIZED');
	}
	
}