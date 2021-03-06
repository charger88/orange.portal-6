<?php

class OPMA_Feedback_Main extends \Orange\Portal\Core\App\Controller
{

	public function indexAction($offset = 0)
	{
		$params = [];
		$params['list'] = OPMM_Feedback_Message::getList([OPMM_Feedback_Message::STATUS_NEW, OPMM_Feedback_Message::STATUS_READ], $offset);
		$params['columns'] = [
			'id' => [],
			'feedback_message_time' => ['width' => 25, 'link' => '_view'],
			'feedback_message_sender_name' => ['width' => 25],
			'feedback_message_subject' => ['width' => 50],
			'_view' => ['title' => '', 'text' => \Orange\Portal\Core\App\Lang::t('ADMIN_EDIT'), 'hint' => \Orange\Portal\Core\App\Lang::t('ADMIN_EDIT'), 'class' => 'icon icon-details', 'link' => '/' . $this->content->getSlug() . '/view/%id%',]
		];
		return $this->templater->fetch('system/admin-list.phtml', $params);
	}

	public function allAction($offset = 0)
	{
		$params = [];
		$params['list'] = OPMM_Feedback_Message::getList(null, $offset);
		$params['columns'] = [
			'id' => [],
			'feedback_message_time' => ['width' => 25, 'link' => '_view'],
			'feedback_message_sender_name' => ['width' => 25],
			'feedback_message_subject' => ['width' => 50],
			'_view' => ['title' => '', 'text' => \Orange\Portal\Core\App\Lang::t('ADMIN_EDIT'), 'hint' => \Orange\Portal\Core\App\Lang::t('ADMIN_EDIT'), 'class' => 'icon icon-details', 'link' => '/' . $this->content->getSlug() . '/view/%id%',]
		];
		return $this->templater->fetch('system/admin-list.phtml', $params);
	}

	public function viewAction($id = 0)
	{
		$item = new OPMM_Feedback_Message(intval($id));
		if ($item->get('feedback_message_status') === OPMM_Feedback_Message::STATUS_NEW) {
			$item->set('feedback_message_status', OPMM_Feedback_Message::STATUS_READ)->save();
		}
		return $this->templater->fetch('feedback/admin-message-view.phtml', [
			'item' => $item,
			'sender' => new \Orange\Portal\Core\Model\User($item->get('feedback_message_sender_user_id')),
			'replier' => new \Orange\Portal\Core\Model\User($item->get('feedback_message_reply_user_id')),
			'form' => new OPMM_Feedback_Form($item->get('feedback_message_form_id')),
		]);
	}

	public function formsAction()
	{
		$params = [];
		$params['list'] = OPMM_Feedback_Form::getList();
		$params['columns'] = [
			'id' => [],
			'feedback_form_name' => ['width' => 75, 'link' => '_edit'],
			'feedback_form_send_to' => ['width' => 25],
			'_edit' => ['title' => '', 'text' => \Orange\Portal\Core\App\Lang::t('ADMIN_EDIT'), 'hint' => \Orange\Portal\Core\App\Lang::t('ADMIN_EDIT'), 'class' => 'icon icon-edit', 'link' => '/' . $this->content->getSlug() . '/edit/%id%',]
		];
		return $this->wrapContentWithTemplate(
			'feedback/admin-feedback-wrapper-forms.phtml',
			$this->templater->fetch('system/admin-list.phtml', $params)
		);
	}

	public function newAction()
	{
		return $this->edit((new OPMM_Feedback_Form())->set('feedback_form_name', \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_DEFAULT_NAME_%s', OPMM_Feedback_Form::getNextNumber())));
	}

	public function editAction($id)
	{
		return $this->edit(new OPMM_Feedback_Form(intval($id)));
	}

	protected function edit($item)
	{
		$form = new OPMX_Feedback_FormEdit();
		$form->setAction($this->content->getURL() . '/save/' . $item->id);
		$form->setValues($item->getData(), true);
		return $form->getHTML();
	}

	public function saveAction($id)
	{
		$item = new OPMM_Feedback_Form(intval($id));
		$form = new OPMX_Feedback_FormEdit();
		$form->setValues($this->getPostArray());
		$item->setData($form->getValuesWithXSRFCheck());
		$item->save();
		$this->log('MODULE_FEEDBACK_FORM_%s_SAVED', [$item->get('feedback_form_name')], 'LOG_FEEDBACK', self::STATUS_OK, $item);
		return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_SAVED'), self::STATUS_OK, $this->content->getURL() . '/edit/' . $item->id);
	}

	public function replyAction($id)
	{
		$message_object = new OPMM_Feedback_Message(intval($id));
		$form_object = new OPMM_Feedback_Form($message_object->get('feedback_message_form_id'));
		$form = new OPMX_Feedback_Reply();
		$form->setAction($this->content->getURL() . '/send/' . $message_object->id);
		if ($message_object->get('feedback_message_reply_text')) {
			$message = $message_object->get('feedback_message_reply_text');
		} else {
			$message = explode("\n", $message_object->get('feedback_message_text'));
			$message = array_map(function ($s) {
				return '> ' . $s;
			}, $message);
			$message = implode("\n", $message);
		}
		$form->setValues([
			'feedback_message_reply_from_email' => $form_object->get('feedback_form_send_to') ? $form_object->get('feedback_form_send_to') : \Orange\Portal\Core\App\Portal::config('system_email_public'),
			'feedback_message_reply_from_name' => \Orange\Portal\Core\App\Portal::config('system_sitename'),
			'feedback_message_reply_text' => $message,
		]);
		return $form->getHTML();
	}

	public function sendAction($id)
	{
		$message_object = new OPMM_Feedback_Message(intval($id));
		$form = new OPMX_Feedback_Reply();
		$form->setAction($this->content->getURL() . '/send/' . $message_object->id);
		$form->setValues($this->getPostArray());
		$message_object
			->setData($form->getValuesWithXSRFCheck())
			->set('feedback_message_status', OPMM_Feedback_Message::STATUS_REPLIED)
			->set('feedback_message_reply_user_id', $this->user->id)
			->set('feedback_message_reply_time', time())
			->save();
		$email = new \Orange\Portal\Core\Net\Email();
		$email->subject = \Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_REPLY_SUBJECT_PREFIX_%s', $message_object->get('feedback_message_subject'));
		$email->html = \Orange\Portal\Core\App\Portal::getInstance()->templater->fetch('email.phtml', [
			'html' => \Orange\Portal\Core\App\Portal::getInstance()->templater->fetch('feedback/default-reply.phtml', [
				'text' => $message_object->get('feedback_message_reply_text'),
			]),
		]);
		$email->plain_text = $message_object->get('feedback_message_reply_text');
		$email->setReturn($message_object->get('feedback_message_reply_from_email'));
		$res = $email->send($message_object->get('feedback_message_sender_email'));
		if ($res) {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_FORM_REPLY_SENT'), self::STATUS_OK, $this->content->getURL() . '/view/' . $message_object->id);
		} else {
			$message_object
				->set('feedback_message_status', OPMM_Feedback_Message::STATUS_READ)
				->save();
			return $this->msg(\Orange\Portal\Core\App\Lang::t('MODULE_FEEDBACK_FORM_REPLY_ERROR'), self::STATUS_ERROR, $this->content->getURL() . '/view/' . $message_object->id);
		}
	}

	public function deleteAction($id)
	{
		$message_object = new OPMM_Feedback_Message(intval($id));
		$message_object->set('feedback_message_status', OPMM_Feedback_Message::STATUS_DELETED);
		$message_object->save();
		return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_DELETED'), self::STATUS_OK, $this->content->getURL());
	}

}