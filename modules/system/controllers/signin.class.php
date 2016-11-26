<?php

class OPMC_System_Signin extends \Orange\Portal\Core\App\Controller
{

	public function indexAction()
	{
		if (!($redirect = $this->getPost('signup_redirect'))) {
			$redirect = $this->arg('error', false) ? $this->getServer('REQUEST_URI') : $this->getHTTPReferer();
		}
		return $this->index($redirect);
	}

	public function indexAjax()
	{
		if (!($redirect = $this->getPost('signup_redirect'))) {
			$redirect = $this->getHTTPReferer();
		}
		return $this->msg($this->index($redirect), self::STATUS_OK);
	}

	public function indexBlock()
	{
		if (!($redirect = $this->getPost('signup_redirect'))) {
			$redirect = $this->getServer('REQUEST_URI');
		}
		return $this->index($redirect);
	}

	private function index($redirect)
	{
		if ($this->user->id) {
			return $this->templater->fetch('system/' . $this->arg('prefix', 'default') . '-authorized.phtml');
		} else {
			$form = new OPMF_System_Signin();
			$form->setValues(['signup_redirect' => $redirect]);
			return $form->getHTML();
		}
	}

	public function offActionDirect()
	{
		\Orange\Portal\Core\App\Portal::getInstance()->session->set('uid', null);
		return $this->redirect(OP_WWW);
	}

}