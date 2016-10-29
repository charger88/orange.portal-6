<?php

class OPMC_News_Main extends OPAL_Controller
{

	public function indexAction()
	{
		return $this->index($this->getGet('offset', 0));
	}

	public function indexAjax()
	{
		return $this->index($this->getGet('offset', 0));
	}

	public function indexBlock()
	{
		return $this->index(0, true);
	}

	public function index($offset = 0, $is_block = false)
	{
		$categoryID = $this->arg('category');
		$list = OPMM_News_Item::getList([
			'types' => ['news_item'],
			'parent_id' => $categoryID,
			'access_user' => $this->user,
			'limit' => $limit = $this->arg('limit', $is_block ? 5 : 10),
			'offset' => $is_block ? 0 : $offset,
			'desc' => true,
		], 'OPMM_News_Item');
		return $this->templater->fetch('news/' . $this->arg('prefix', 'default') . '-' . ($is_block ? 'block' : 'digest') . '.phtml', [
			'list' => $list,
			'offset' => $offset,
			'limit' => $limit,
			'digestlink' => $this->arg('digestlink', false),
			'category' => $categoryID ? new OPAM_Page($categoryID) : ($is_block ? new OPAM_Page() : $this->content),
		]);
	}

	public function viewAction()
	{
		return $this->templater->fetch('news/' . $this->arg('prefix', 'default') . '-view.phtml', [
			'content' => $this->content,
			'media' => new OPMM_System_Media($this->content->get('content_image')),
		]);
	}

}