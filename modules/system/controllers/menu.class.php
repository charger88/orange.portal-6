<?php

class OPMC_System_Menu extends \Orange\Portal\Core\App\Controller
{

	protected $cachemap = [
		'indexAction' => ['by_user_access', 'id_is_arg' => 'root'],
		'indexAjax' => ['by_user_access', 'id_is_arg' => 'root'],
		'indexBlock' => ['by_user_access', 'id_is_arg' => 'root'],
		'treeAction' => ['by_user_access', 'id_is_arg' => 'root'],
		'treeAjax' => ['by_user_access', 'id_is_arg' => 'root'],
		'treeBlock' => ['by_user_access', 'id_is_arg' => 'root'],
	];

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
		return $this->index();
	}

	private function index()
	{
		return $this->templater->fetch('system/' . $this->arg('prefix', 'default') . '-menu.phtml', [
			'menu' => \Orange\Portal\Core\Model\Page::getMenu($this->user, $this->getRoot())
		]);
	}

	public function treeAction()
	{
		return $this->tree();
	}

	public function treeAjax()
	{
		return $this->tree();
	}

	public function treeBlock()
	{
		return $this->tree();
	}

	private function tree()
	{
		$levels = $this->arg('levels', 0);
		$root = $this->getRoot();
		return $this->templater->fetch('system/' . $this->arg('prefix', 'default') . '-menu-tree.phtml', [
			'menu' => \Orange\Portal\Core\Model\Page::getTreeMenu($this->user, \Orange\Portal\Core\App\Portal::$sitelang, $root, $levels),
			'root' => $root,
			'levels' => $levels,
		]);
	}

	private function getRoot()
	{
		$root_id = $this->arg('root', 0);
		if ($root_id === '{page_id}') {
			$root_id = \Orange\Portal\Core\App\Portal::getInstance()->content->id;
		} else if ($root_id === '{parent_id}') {
			$root_id = \Orange\Portal\Core\App\Portal::getInstance()->content->get('content_parent_id');
		}
		return $root_id;
	}

}