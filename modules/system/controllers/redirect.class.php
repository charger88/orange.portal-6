<?php

class OPMC_System_Redirect extends OPAL_Controller
{

	public function indexAction()
	{
		return $this->index();
	}

	private function index()
	{
		$url = $this->arg('url', OP_WWW);
		if ($url{0} === '/') {
			$url = OP_WWW . $url;
		}
		$permanent = (bool)$this->arg('permanent', false);
		return $this->redirect($url, $permanent);
	}

}