<?php

use Orange\Forms\Components\Fieldset;
use Orange\Forms\Components\Multirow;
use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Html;
use Orange\Forms\Fields\Inputs\Datetime;
use Orange\Forms\Fields\Inputs\Hidden;
use Orange\Forms\Fields\Inputs\Text;
use Orange\Forms\Fields\Inputs\Textarea;
use Orange\Forms\Fields\Selectors\Checkbox;
use Orange\Forms\Fields\Selectors\Select;
use Orange\Forms\Form;

class OPMX_System_ContentEdit extends Form
{

	private $lang_overwrite = [];

	/**
	 * @param array $params
	 */
	protected function init($params)
	{

		/** @var OPAM_Content_Type $type */
		$type = $params['type'];

		$this->lang_overwrite = isset($params['lang_overwrite']) ? $params['lang_overwrite'] : [];

		$params['hide'] = $type->get('content_type_hidden');
		$params['options'] = isset($params['options']) ? $params['options'] : [];

		/* Top BEGIN */

		$this->addField((new Text('content_title', $this->lng('content_title')))
			->addAttributes(['class' => 'input-title']), 'header');

		/* Top EMD */

		/* Column BEGIN */

		if (!in_array('content_lang', $params['hide']) && (count($params['options']['content_lang']) > 1)) {
			$fieldset = new Fieldset('fieldset-lang', $this->lng('LANG_OPTIONS'));
			$fieldset->addField((new Select('content_lang', $this->lng('content_lang')))
				->setOptions(isset($params['options']['content_lang']) ? $params['options']['content_lang'] : [])
				->setEmptyOption(Select::EMPTY_OPTION_ALWAYS));
			$fieldset->addField((new Select('content_default_lang_id', $this->lng('content_default_lang_id')))
				->setOptions(isset($params['options']['content_default_lang_id']) ? ([0 => ''] + $params['options']['content_default_lang_id']) : []));
			$this->addField($fieldset, 'column');
		}

		if (!in_array('content_status', $params['hide']) || !in_array('content_time_published', $params['hide'])) {
			$fieldset = new Fieldset('fieldset-publishing', $this->lng('PUBLISHING'));
			if (!in_array('content_status', $params['hide']) && !empty($params['options']['content_status'])) {
				$fieldset->addField((new Select('content_status', $this->lng('content_status')))
					->requireField()
					->setOptions(isset($params['options']['content_status']) ? $params['options']['content_status'] : []));
			}
			if (!in_array('content_time_published', $params['hide'])) {
				$fieldset->addField((new Datetime('content_time_published', $this->lng('content_time_published'))));
			}
			$this->addField($fieldset, 'column');
		}

		if (!in_array('content_access_groups', $params['hide'])) {
			$fieldset = new Fieldset('access', $this->lng('ACCESS'));
			if (!empty($params['options']['content_access_groups'])) {
				foreach ($params['options']['content_access_groups'] as $group_id => $group_name) {
					$fieldset->addField((new Checkbox('content_access_groups_' . $group_id, $this->lng($group_name)))
						->setName('content_access_groups[]')
						->setDefault($group_id));
				}
			}
			$this->addField($fieldset, 'column');
		}

		if (!in_array('content_parent_id', $params['hide']) || !in_array('content_tags', $params['hide'])) {
			$fieldset = new Fieldset('structure', $this->lng('STRUCTURE'));
			if (!in_array('content_parent_id', $params['hide'])) {
				$fieldset->addField((new Select('content_parent_id', $this->lng('content_parent_id')))
					->setOptions(isset($params['options']['content_parent_id']) ? $params['options']['content_parent_id'] : [])
				);
			}
			if (!in_array('content_tags', $params['hide'])) {
				$fieldset->addField((new Text('content_tags', $this->lng('content_tags'))));
			}
			$this->addField($fieldset, 'column');
		}

		if (
			!in_array('content_area', $params['hide']) ||
			!in_array('content_on_site_mode', $params['hide']) ||
			!in_array('content_image', $params['hide']) ||
			!in_array('content_template', $params['hide'])
		) {

			$fieldset = new Fieldset('view', $this->lng('VIEW'));

			if (!in_array('content_area', $params['hide'])) {
				$fieldset->addField((new Select('content_area', $this->lng('content_area')))
					->setOptions(isset($params['options']['content_area']) ? $params['options']['content_area'] : []));
			}

			if (!in_array('content_on_site_mode', $params['hide']) && !empty($params['options']['content_on_site_mode'])) {
				$fieldset->addField((new Select('content_on_site_mode', $this->lng('content_on_site_mode')))
					->requireField()
					->setOptions(isset($params['options']['content_on_site_mode']) ? $params['options']['content_on_site_mode'] : []));
			}

			if (!in_array('content_image', $params['hide'])) {
				$fieldset->addField((new Text('content_image', $this->lng('content_image')))
					->setAttributes([
						'class' => 'op-media'
					]), 'column');
			}

			if (!in_array('content_template', $params['hide'])) {
				$content_template = new Select('content_template', $this->lng('content_template'));
				$content_template->setOptions(isset($params['options']['content_template']) ? $params['options']['content_template'] : []);
				if ($type->get('content_type_type') == 1) {
					$content_template->requireField();
				}
				$fieldset->addField($content_template, 'column');
			}

			$this->addField($fieldset, 'column');

		}

		if ($ctfields = $type->get('content_type_fields')) {
			$last_field_group = '';
			$fieldset = null;
			foreach ($ctfields as $field_id => $field) {
				if ($last_field_group != $field['group']) {
					if ($fieldset) {
						$this->addField($fieldset, 'column');
					}
					$fieldset = new Fieldset('fieldset-' . md5($field['group']), $this->lng($field['group']));
				}
				$field_id = 'content_field_' . $field_id;
				$field_label = $this->lng($field['title']);
				switch ($field['type']) {
					case 'BOOLEAN':
						$fobj = (new Checkbox($field_id, $field_label))->setDefault(1);
						break;
					case 'NLLIST':
						$fobj = (new Textarea($field_id, $field_label));
						break;
					default:
						$fobj = (new Text($field_id, $field_label));
						break;
				}
				$fieldset->addField($fobj);
				$last_field_group = $field['group'];
			}
			if ($fieldset) {
				$this->addField($fieldset, 'column');
			}
		}

		/* Column END */

		/* Main BEGIN */

		if (!in_array('content_slug', $params['hide'])) {
			$content_slug = new Text('content_slug', $this->lng('content_slug'));
			if (!empty($params['options']['content_template'])) {
				$field_options = array_keys($params['options']['content_template']);
				if (strpos($field_options[0], 'block-') !== 0) {
					$content_slug->addAttributes([
						'data-postfix' => '.html'
					]);
				}
			}
			$this->addField($content_slug, 'main');
		}

		if ($texts = $type->get('content_type_texts')) {
			foreach ($texts as $text_id => $text_name) {
				$this->addField((new Textarea('content_text_' . $text_id, $this->lng($text_name)))
					->addAttributes(['class' => 'wysiwyg' . ($text_id == 'text' ? ' full-text' : '')]), 'main');
				$this->addField((new Select('content_text_' . $text_id . '_format', $this->lng('content_text_format')))
					->requireField()
					->setOptions(isset($params['options']['content_text_format']) ? $params['options']['content_text_format'] : []), 'main');
			}
		}

		if (!in_array('content_commands', $params['hide'])) {

			$multirow = new Multirow('content_commands', OPAL_Lang::t('content_commands'));
			{
				$multirow->addField((new Text('content_commands_controller', OPAL_Lang::t('content_commands:module')))->setName('module'));
				$multirow->addField((new Text('content_commands_method', OPAL_Lang::t('content_commands:controller')))->setName('controller'));
				$multirow->addField((new Text('content_commands_static', OPAL_Lang::t('content_commands:method')))->setName('method'));
				$multirow->addField((new Text('content_commands_module', OPAL_Lang::t('content_commands:static')))->setName('static'));
				$multirow->addField((new Text('content_commands_args', OPAL_Lang::t('content_commands:args')))->setName('args'));
			}
			$this->addField($multirow, 'main');

		}

		/* Main END */

		/* Buttons BEGIN */

		$this->addField((new Hidden('content_type')), 'top');

		$this->addField((new Submit('content_edit_submit', OPAL_Lang::t('ADMIN_SAVE'))), 'top');

		/* Buttons END */

		$this->addField(new Html(OPAL_Portal::getInstance()->templater->fetch(
			'system/admin-media-init.phtml'
		)));

		$this->enableXSRFProtection();

		OPAL_Theme::addScriptFile('module/admin/system/content/commands?type=' . $type->get('content_type_code') . '&key=' . \Orange\Forms\XSRFProtection::getInstance()->key(['content_commands']));
		OPAL_Theme::addScriptFile('modules/system/static/js/admin-content-form.js');

	}

	public function getValues()
	{
		$values = parent::getValues();
		if (isset($values['content_commands'])) {
			$content_commands = $values['content_commands'];
			$values['content_commands'] = [];
			$first_column = $content_commands[key($content_commands)];
			foreach ($first_column as $i => $id) {
				if (!empty($id) && !empty($content_commands['controller'][$i]) && !empty($content_commands['method'][$i])) {
					$values['content_commands'][] = [
						'module' => $id,
						'controller' => $content_commands['controller'][$i],
						'method' => $content_commands['method'][$i],
						'static' => $content_commands['static'][$i],
						'args' => $content_commands['args'][$i] ? json_decode($content_commands['args'][$i], true) : [],
					];
				}
			}
		}
		return $values;
	}

	public function setValues($values, $from_db = false)
	{
		if ($from_db) {
			if (isset($values['content_commands'])) {
				$content_commands = $values['content_commands'];
				$values['content_commands'] = [];
				$values['content_commands']['module'] = [];
				$values['content_commands']['controller'] = [];
				$values['content_commands']['method'] = [];
				$values['content_commands']['static'] = [];
				$values['content_commands']['args'] = [];
				foreach ($content_commands as $i => $row) {
					$values['content_commands']['module'][$i] = $row['module'];
					$values['content_commands']['controller'][$i] = $row['controller'];
					$values['content_commands']['method'][$i] = $row['method'];
					$values['content_commands']['static'][$i] = $row['static'];
					$values['content_commands']['args'][$i] = json_encode($row['args']);
				}
			}
		}
		return parent::setValues($values);
	}


	private function lng($key)
	{
		return OPAL_Lang::t(isset($this->lang_overwrite[$key]) ? $this->lang_overwrite[$key] : $key);
	}

}