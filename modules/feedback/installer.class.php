<?php

class OPMI_Feedback extends \Orange\Portal\Core\App\Installer
{

	public function installModule()
	{
		$this->errors = [];
		if (empty($this->errors)) {
			$this->createThisModule();
		}
		if (empty($this->errors)) {
			OPMM_Feedback_Form::install();
			OPMM_Feedback_Message::install();
		}
		if (empty($this->errors)) {
			$this->createContent();
		}
		return $this->errors;
	}

	private function createContent()
	{
		$result = true;
		$id = (new \Orange\Portal\Core\Model\Content())
			->setData([
				'content_type' => 'admin',
				'content_title' => 'MODULE_FEEDBACK',
				'content_access_groups' => [1, 2],
				'content_lang' => '',
				'content_slug' => 'admin/feedback',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'feedback', 'controller' => 'admin-main', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
				'content_time_published' => time(),
				'content_user_id' => 1,
			])
			->save()
			->id;
		if (!$id) {
			return false;
		}
		(new OPMM_Feedback_Form())
			->setData([
				'feedback_form_name' => 'Default form',
				'feedback_form_themes' => '',
				'feedback_form_theme' => OPMM_Feedback_Form::FIELD_STATUS_ENABLED,
				'feedback_form_phone' => OPMM_Feedback_Form::FIELD_STATUS_ENABLED,
				'feedback_form_email' => OPMM_Feedback_Form::FIELD_STATUS_REQUIRED,
				'feedback_form_text' => OPMM_Feedback_Form::FIELD_STATUS_REQUIRED,
				'feedback_form_fields' => [],
			])
			->save();
		return $result;
	}

	public function createdAdditionalContent()
	{
		$lang = \Orange\Portal\Core\App\Portal::config('system_default_lang', 'en');
		\Orange\Portal\Core\App\Lang::load('modules/feedback/lang/admin', $lang);
		$content = new \Orange\Portal\Core\Model\Page();
		$content->setData([
			'content_title' => \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK'),
			'content_access_groups' => [0],
			'content_lang' => $lang,
			'content_slug' => 'feedback.html',
			'content_on_site_mode' => 3,
			'content_status' => 6,
			'content_time_published' => time(),
			'content_commands' => [['module' => 'feedback', 'controller' => 'main', 'method' => 'index', 'static' => false, 'args' => []],],
			'content_template' => 'main-html.phtml',
			'content_user_id' => 1,
		]);
		$content->save();
	}

}