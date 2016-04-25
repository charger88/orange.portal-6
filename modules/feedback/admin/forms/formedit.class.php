<?php

class OPMX_Feedback_FormEdit extends OPAL_Form {
		
	protected function build($params){

        $field_status_ref = [
            0 => OPAL_Lang::t('MODULE_FEEDBACK_FIELD_STATUS_DISABLED'),
            1 => OPAL_Lang::t('MODULE_FEEDBACK_FIELD_STATUS_ENABLED'),
            2 => OPAL_Lang::t('MODULE_FEEDBACK_FIELD_STATUS_REQUIRED'),
        ];

        $this->addField('feedback_form_theme', 'select', OPAL_Lang::t('feedback_form_theme'), ['options' => $field_status_ref], 'column');
        $this->addField('feedback_form_phone', 'select', OPAL_Lang::t('feedback_form_phone'), ['options' => $field_status_ref], 'column');
        $this->addField('feedback_form_email', 'select', OPAL_Lang::t('feedback_form_email'), ['options' => $field_status_ref], 'column');
        $this->addField('feedback_form_uname', 'select', OPAL_Lang::t('feedback_form_uname'), ['options' => $field_status_ref], 'column');
        $this->addField('feedback_form_text',  'select', OPAL_Lang::t('feedback_form_text'),  ['options' => $field_status_ref], 'column');

        $this->addField('feedback_form_name', 'text', OPAL_Lang::t('feedback_form_name'), array('required' => true), 'main');
        $this->addField('feedback_form_themes', 'textarea', OPAL_Lang::t('feedback_form_themes'), [], 'main');
        $this->addField('feedback_form_fields', 'textarea', OPAL_Lang::t('feedback_form_fields'), [], 'main');
        $this->addField('feedback_form_send_to', 'text', OPAL_Lang::t('feedback_form_send_to'), [], 'main');

        $this->addField('feedback_form_fields:name', 'text', OPAL_Lang::t('feedback_form_fields:name'), [], 'feedback_form_fields[]');
        $this->addField('feedback_form_fields:status', 'select', OPAL_Lang::t('feedback_form_fields:status'), ['options' => $field_status_ref], 'feedback_form_fields[]');
        $this->addMultirow('feedback_form_fields', 'main');

        $this->addField('feedback_form_edit_submit', 'submit', OPAL_Lang::t('ADMIN_SAVE'), array(), 'buttons');

	}
		
}