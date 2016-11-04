<?php

class OPMC_System_Text extends OPAL_Controller
{


	public function indexAction()
	{
		return $this->index();
	}

	public function indexAjax()
	{
		return $this->index();
	}

	public function indexBlock()
	{
		return $this->index(true);
	}

	private function index($is_block = false)
	{
		return $this->templater->fetch('system/' . $this->arg('prefix', 'default') . '-text-' . ($is_block ? 'block' : 'page') . '.phtml', [
			'content' => $this->content
		]);
	}

	public function dataAction()
	{
		return $this->data();
	}

	public function dataAjax()
	{
		return $this->data();
	}

	public function dataBlock()
	{
		return $this->data();
	}

	private function data()
	{
		return $this->content->text('text')->format();
	}

}