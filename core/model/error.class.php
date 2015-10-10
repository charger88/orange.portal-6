<?php

/**
 * Class OPAM_Error
 */
class OPAM_Error extends OPAM_Content {

    /**
     * @return int|null
     */
    public function save(){
		$this->set('content_type', 'error');
		$this->set('content_template', 'main-error.phtml');
		return parent::save();
	}

}