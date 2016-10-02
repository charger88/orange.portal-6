<?php

class OPMI_Feedback extends OPAL_Installer {
	
	public function installModule(){
		$this->errors = array();
		if (empty($this->errors)){
			$this->createThisModule();
		}
        if (empty($this->errors)) {
            OPMM_Feedback_Form::install();
            OPMM_Feedback_Message::install();
        }
        if (empty($this->errors)){
            $this->createContent();
        }
		return $this->errors;
	}

    private function createContent(){
        $result = true;
        $id = (new OPAM_Content())
            ->setData([
                'content_type'           => 'admin',
                'content_title'          => 'MODULE_FEEDBACK',
                'content_access_groups'  => array(1,2),
                'content_lang'           => '',
                'content_slug'           => 'admin/feedback',
                'content_on_site_mode'   => 0,
                'content_status'         => 6,
                'content_commands'       => array( array( 'module' => 'feedback', 'controller' => 'admin-main', 'method' => '', 'static' => false, 'args' => array() ), ),
                'content_template'       => 'main-admin.phtml',
                'content_time_published' => time(),
                'content_user_id'        => 1,
            ])
            ->save()
            ->id
        ;
        if (!$id){
            return false;
        }
        (new OPMM_Feedback_Form())
            ->setData([
                'feedback_form_name'   => 'Default form',
                'feedback_form_themes' => '',
                'feedback_form_theme'  => OPMM_Feedback_Form::FIELD_STATUS_ENABLED,
                'feedback_form_phone'  => OPMM_Feedback_Form::FIELD_STATUS_ENABLED,
                'feedback_form_email'  => OPMM_Feedback_Form::FIELD_STATUS_REQUIRED,
                'feedback_form_text'   => OPMM_Feedback_Form::FIELD_STATUS_REQUIRED,
                'feedback_form_fields' => [],
            ])
            ->save()
        ;
        return $result;
    }
	
}