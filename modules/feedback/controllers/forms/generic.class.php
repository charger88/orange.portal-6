<?php

class OPMF_Feedback_Generic extends OPAL_Form {
		
	protected function build($params){
        if ($params['feedback_form_theme']) {
            $req = $params['feedback_form_theme'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED;
            if (empty(trim($params['feedback_form_themes']))) {
                $this->addField('theme', 'text', OPAL_Lang::t('MODULE_FEEDBACK_THEME'), ['required' => $req]);
            } else {
                $themes = explode("\n",$params['feedback_form_themes']);
                $themes = array_combine($themes,$themes);
                if (count($themes) == 1){
                    $this->addField('theme', 'hidden', OPAL_Lang::t('MODULE_FEEDBACK_THEME'), ['required' => $req, 'value' => $themes[0]]);
                } else {
                    $this->addField('theme', 'select', OPAL_Lang::t('MODULE_FEEDBACK_THEME'), ['required' => $req, 'options' => $themes]);
                }
            }
        }

        if ($params['feedback_form_fields']) {
            foreach ($params['feedback_form_fields'] as $field){
                $req = $field['status'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED;
                $this->addField('field_'.md5($field['name']), 'text', OPAL_Lang::t($field['name']), ['required' => $req] );
            }
        }

        if ($params['feedback_form_phone']) {
            $req = $params['feedback_form_phone'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED;
            $this->addField('phone', 'text', OPAL_Lang::t('MODULE_FEEDBACK_PHONE'), ['required' => $req] );
        }
        if ($params['feedback_form_email']) {
            $req = $params['feedback_form_email'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED;
            $this->addField('email', 'email', OPAL_Lang::t('MODULE_FEEDBACK_EMAIL'), ['required' => $req] );
        }
        if ($params['feedback_form_uname']) {
            $req = $params['feedback_form_uname'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED;
            $this->addField('uname', 'text', OPAL_Lang::t('MODULE_FEEDBACK_UNAME'), ['required' => $req] );
        }
        if ($params['feedback_form_text']) {
            $req = $params['feedback_form_text'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED;
            $this->addField('text', 'textarea', OPAL_Lang::t('MODULE_FEEDBACK_TEXT'), ['value' => '', 'required' => $req] );
        }
		$this->addField(null, 'submit', OPAL_Lang::t('MODULE_FEEDBACK_SEND'));
	}
	
}