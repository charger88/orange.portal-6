<?php

class OPMC_System_Text extends OPAL_Controller {
	
	
	public function indexAction(){
		return $this->index();
	}
	
	public function indexAjax(){
		return $this->index();
	}
	
	public function indexBlock(){
		return $this->index(true);
	}
	
	private function index($is_block = false){
		return $this->templater->fetch('system/'.$this->arg('prefix','default').'-text-'.($is_block ? 'block' : 'page').'.phtml',array(
			'content' => $this->content
		));
	}
	
}