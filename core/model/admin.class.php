<?php

/**
 * Class OPAM_Admin
 */
class OPAM_Admin extends OPAM_Content {

    /**
     * @return int|null
     */
    public function save(){
		$this->set('content_type', 'admin');
		$this->set('content_template', 'main-admin.phtml');
		return parent::save();
	}

}