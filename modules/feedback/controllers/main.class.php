<?php

class OPMC_Feedback_Main extends OPAL_Controller
{

	public function indexAction()
	{
		return $this->index();
	}

	public function indexAjax()
	{
		return $this->index();
	}

	public function indexBlock()
	{
		return $this->index();
	}

	protected function index()
	{
		$form_id = intval($this->arg('form', 1));
		$form_object = new OPMM_Feedback_Form($form_id);
		if ($form_object->id) {
			$form = new OPMF_Feedback_Generic($form_object->getData());
			$form->setAction(OP_WWW . '/module/feedback/main/send/' . $form_object->id);
			if ($formValues = $this->getGet('formValues')) {
				if ($formValues = base64_decode($formValues)) {
					if ($formValues = json_decode($formValues, true)) {
						$form->setValues($formValues);
					}
				}
			}
			$this->session->set('feedback_spam', $this->getFormToken(time()));
			return $form->getHTML();
		} else {
			$this->log(OPAL_Lang::t('MODULE_FEEDBACK_NO_FORM'), [], 'LOG_FEEDBACK', self::STATUS_WARNING);
			return $this->msg(OPAL_Lang::t('MODULE_FEEDBACK_NO_FORM'), self::STATUS_NOTFOUND);
		}
	}

	public function sendActionDirect($form_id)
	{
		return $this->send($form_id);
	}

	public function sendAjaxDirect($form_id)
	{
		return $this->send($form_id);
	}

	protected function send($form_id)
	{
		$form_object = new OPMM_Feedback_Form(intval($form_id));
		if ($form_object->id) {
			$form = new OPMF_Feedback_Generic($form_object->getData());
			$form->setAction(OP_WWW . '/module/feedback/main/send/' . $form_object->id);
			$form->setValues($this->getPostArray());
			if ($form->checkXSRF()) {
				if ($errors = $form->validateValues()->getErrors()) {
					if (OPAL_Portal::getInstance()->env('ajax')) {
						return $this->msg(OPAL_Lang::t('MODULE_FEEDBACK_ERROR'), self::STATUS_ERROR, null, ['errors' => $errors]);
					} else {
						return $form->getHTML();
					}
				} else {
					if (in_array($this->session->get('feedback_spam'), $this->getAllowedTokens())) {
						$fields = [];
						if ($fields_from = $form_object->get('feedback_form_fields')) {
							foreach ($fields_from as $field) {
								$fields[$field['name']] = $this->getPost('field_' . md5($field['name']), '');
							}
						}
						$message = new OPMM_Feedback_Message();
						$message
							->setData([
								'feedback_message_status' => 0,
								'feedback_message_subject' => $this->getPost('theme', ''),
								'feedback_message_text' => $this->getPost('text', ''),
								'feedback_message_form_id' => $form_object->id,
								'feedback_message_fields' => $fields,
								'feedback_message_time' => time(),
								'feedback_message_sender_user_id' => $this->user->id,
								'feedback_message_sender_name' => $this->getPost('uname', $this->user->get('user_name')),
								'feedback_message_sender_email' => $this->getPost('email', $this->user->get('user_email')),
								'feedback_message_sender_phone' => $this->getPost('phone', $this->user->get('user_phone')),
								'feedback_message_sender_ip' => $this->getIP(),
								'feedback_message_sender_session' => OPAL_Portal::getInstance()->session->id()
							])
							->save();
						static::sendMessage($message, $form_object);
						$this->session->set('feedback_spam', null);
						$this->log('MODULE_FEEDBACK_MESSAGE_SENT', [], 'LOG_FEEDBACK', self::STATUS_OK);
						if (OPAL_Portal::env('ajax')) {
							return $this->msg(OPAL_Lang::t('MODULE_FEEDBACK_MESSAGE_SENT'), self::STATUS_OK);
						} else {
							return $this->redirect(OP_WWW . '/module/feedback/main/sent');
						}
					} else {
						$this->log('MODULE_FEEDBACK_SPAM_WRONG_TOKEN', [], 'LOG_FEEDBACK', self::STATUS_INFO);
						return $this->msg(OPAL_Lang::t('MODULE_FEEDBACK_SPAM_DETECTED'), self::STATUS_WARNING);
					}
				}
			} else {
				return $this->msg(OPAL_Lang::t('ADMIN_XSRF'), self::STATUS_WARNING);
			}
		} else {
			$this->log('MODULE_FEEDBACK_NO_FORM', [], 'LOG_FEEDBACK', self::STATUS_NOTFOUND);
			return $this->msg(OPAL_Lang::t('MODULE_FEEDBACK_NO_FORM'), self::STATUS_NOTFOUND);
		}
	}

	public function sentActionDirect()
	{
		return $this->msg(OPAL_Lang::t('MODULE_FEEDBACK_MESSAGE_SENT'), self::STATUS_OK);
	}

	protected function getFormToken($time)
	{
		return md5(date("YmdH", $time) . '-' . $this->getIP() . '-feedback-' . OPAL_Portal::config('system_secretkey'));
	}

	protected function getAllowedTokens()
	{
		$tokens = [];
		for ($i = 0; $i < 4; $i++) {
			$tokens[] = $this->getFormToken(time() - $i * 3600);
		}
		return $tokens;
	}

	public static function encodeDataForForm($data)
	{
		return base64_encode(json_encode($data));
	}

	public static function sendMessage($message, $form = null, $template = 'feedback/default-feedback.phtml')
	{
		if (is_null($form)) {
			$form = new OPMM_Feedback_Form($message->get('feedback_message_form_id'));
		}
		$email = new OPAL_Email();
		$email->subject = $form->get('feedback_form_name') . ' / ' . $message->get('feedback_message_subject');
		$email->html = OPAL_Portal::getInstance()->templater->fetch('email.phtml', [
			'html' => OPAL_Portal::getInstance()->templater->fetch($template, [
				'message' => $message,
			]),
		]);
		$email->setReturn($message->get('feedback_message_sender_email'));
		return $email->send(
			$form->get('feedback_form_send_to')
				? $form->get('feedback_form_send_to')
				: OPAL_Portal::config('system_email_public')
		);
	}

}