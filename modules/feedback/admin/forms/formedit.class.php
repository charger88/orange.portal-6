<?php

use Orange\Forms\Components\Multirow;
use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Inputs\Text;
use Orange\Forms\Fields\Inputs\Textarea;
use Orange\Forms\Fields\Selectors\Select;
use Orange\Forms\Form;

class OPMX_Feedback_FormEdit extends Form
{

	protected function init($params)
	{

		$field_status_ref = [
			0 => \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_FIELD_STATUS_DISABLED'),
			1 => \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_FIELD_STATUS_ENABLED'),
			2 => \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_FIELD_STATUS_REQUIRED'),
		];

		$this->addField((new Select('feedback_form_theme', \Orange\Portal\Core\App\Lang::t('feedback_form_theme')))->setOptions($field_status_ref), 'column');
		$this->addField((new Select('feedback_form_phone', \Orange\Portal\Core\App\Lang::t('feedback_form_phone')))->setOptions($field_status_ref), 'column');
		$this->addField((new Select('feedback_form_email', \Orange\Portal\Core\App\Lang::t('feedback_form_email')))->setOptions($field_status_ref), 'column');
		$this->addField((new Select('feedback_form_uname', \Orange\Portal\Core\App\Lang::t('feedback_form_uname')))->setOptions($field_status_ref), 'column');
		$this->addField((new Select('feedback_form_text', \Orange\Portal\Core\App\Lang::t('feedback_form_text')))->setOptions($field_status_ref), 'column');

		$this->addField((new Text('feedback_form_name', \Orange\Portal\Core\App\Lang::t('feedback_form_name')))->requireField(), 'main');
		$this->addField((new Textarea('feedback_form_themes', \Orange\Portal\Core\App\Lang::t('feedback_form_themes'))), 'main');
		$this->addField((new Text('feedback_form_send_to', \Orange\Portal\Core\App\Lang::t('feedback_form_send_to'))), 'main');

		$multirow = new Multirow('feedback_form_fields', \Orange\Portal\Core\App\Lang::t('feedback_form_fields'));
		$multirow->addField((new Text('feedback_form_fields_name', \Orange\Portal\Core\App\Lang::t('feedback_form_fields:name')))->setName('name'));
		$multirow->addField((new Text('feedback_form_fields_status', \Orange\Portal\Core\App\Lang::t('feedback_form_fields:status')))->setName('status'));
		$this->addField($multirow, 'main');

		$this->addField((new Submit('feedback_form_edit_submit', \Orange\Portal\Core\App\Lang::t('ADMIN_SAVE'))), 'top');

		$this->enableXSRFProtection();

	}

	public function getValues()
	{
		$values = parent::getValues();
		$feedback_form_fields = $values['feedback_form_fields'];
		$values['feedback_form_fields'] = [];
		$first_column = $feedback_form_fields[key($feedback_form_fields)];
		foreach ($first_column as $i => $name) {
			if (!empty($name) && !empty($feedback_form_fields['status'][$i])) {
				$values['feedback_form_fields'][] = [
					'name' => $name,
					'status' => $feedback_form_fields['status'][$i],
				];
			}
		}
		return $values;
	}

	public function setValues($values, $from_db = false)
	{
		if ($from_db) {
			$feedback_form_fields = $values['feedback_form_fields'];
			$values['feedback_form_fields'] = [];
			$values['feedback_form_fields']['name'] = [];
			$values['feedback_form_fields']['status'] = [];
			foreach ($feedback_form_fields as $i => $row) {
				$values['feedback_form_fields']['name'][$i] = $row['name'];
				$values['feedback_form_fields']['status'][$i] = $row['status'];
			}
		}
		return parent::setValues($values);
	}

}