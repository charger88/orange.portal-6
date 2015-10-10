<?php

class OPMC_System_Menu extends OPAL_Controller {
	
	protected $cachemap = array(
		'indexAction' => array('by_user_access','id_is_arg' => 'root'),
		'indexAjax' => array('by_user_access','id_is_arg' => 'root'),
		'indexBlock' => array('by_user_access','id_is_arg' => 'root'),
		'treeAction' => array('by_user_access','id_is_arg' => 'root'),
		'treeAjax' => array('by_user_access','id_is_arg' => 'root'),
		'treeBlock' => array('by_user_access','id_is_arg' => 'root'),
	);
	
	public function indexAction(){
		return $this->index();
	}
	
	public function indexAjax(){
		return $this->index();
	}
	
	public function indexBlock(){
		return $this->index();
	}
	
	private function index(){
		return $this->templater->fetch('system/'.$this->arg('prefix','default').'-menu.phtml',array(
			'menu' => OPAM_Page::getMenu($this->user)
		));
	}
	
	public function treeAction(){
		return $this->tree();
	}
	
	public function treeAjax(){
		return $this->tree();
	}
	
	public function treeBlock(){
		return $this->tree();
	}
	
	private function tree(){
		return $this->templater->fetch('system/'.$this->arg('prefix','default').'-menu-tree.phtml',array(
			'menu' => OPAM_Page::getTreeMenu($this->user,OPAL_Portal::$sitelang,$this->arg('root', 0)),
			'root' => $this->arg('root', 0),
		));
	}
	
}