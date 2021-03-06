<?php

use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Inputs\Email;
use Orange\Forms\Fields\Inputs\Hidden;
use Orange\Forms\Fields\Inputs\Text;
use Orange\Forms\Fields\Inputs\Textarea;
use Orange\Forms\Fields\Selectors\Select;
use Orange\Forms\Form;

class OPMF_Feedback_Generic extends Form
{

	protected function init($params)
	{
		if ($params['feedback_form_theme']) {
			$req = $params['feedback_form_theme'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED;
			if (empty(trim($params['feedback_form_themes']))) {
				$field = new Text('theme', \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_THEME'));
				if ($req) {
					$field->requireField();
				}
			} else {
				$themes = explode("\n", $params['feedback_form_themes']);
				$themes = array_combine($themes, $themes);
				if (count($themes) == 1) {
					$field = new Hidden('theme');
					$field->setDefault(current($themes));
				} else {
					$field = new Select('theme', \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_THEME'));
					if ($req) {
						$field->requireField();
					}
					$field->setOptions($themes);
				}
			}
			$this->addField($field);
		}

		if ($params['feedback_form_fields']) {
			foreach ($params['feedback_form_fields'] as $field) {
				$req = $field['status'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED;
				$field = new Text('field_' . md5($field['name']), \Orange\Portal\Core\App\Lang::t($field['name']));
				if ($req) {
					$field->requireField();
				}
				$this->addField($field);
			}
		}

		if ($params['feedback_form_phone']) {
			$field = new Text('phone', \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_PHONE'));
			if ($params['feedback_form_phone'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED) {
				$field->requireField();
			}
			$this->addField($field);
		}
		if ($params['feedback_form_email']) {
			$field = new Email('email', \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_EMAIL'));
			if ($params['feedback_form_email'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED) {
				$field->requireField();
			}
			$this->addField($field);
		}
		if ($params['feedback_form_uname']) {
			$field = new Text('uname', \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_UNAME'));
			if ($params['feedback_form_uname'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED) {
				$field->requireField();
			}
			$this->addField($field);
		}
		if ($params['feedback_form_text']) {
			$field = new Textarea('text', \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_TEXT'));
			if ($params['feedback_form_text'] == OPMM_Feedback_Form::FIELD_STATUS_REQUIRED) {
				$field->requireField();
			}
			$this->addField($field);
		}

		$this->addField(new Submit('feedback_form_submit', \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_SEND')));

		$this->enableXSRFProtection();
	}

}