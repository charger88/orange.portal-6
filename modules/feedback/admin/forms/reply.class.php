<?php

use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Inputs\Text;
use Orange\Forms\Fields\Inputs\Textarea;
use Orange\Forms\Form;

class OPMX_Feedback_Reply extends Form
{

	protected function init($params)
	{

		$this->addField((new Text('feedback_message_reply_from_name', \Orange\Portal\Core\App\Lang::t('feedback_message_reply_from_name')))->requireField());
		$this->addField((new Text('feedback_message_reply_from_email', \Orange\Portal\Core\App\Lang::t('feedback_message_reply_from_email')))->requireField());
		$this->addField((new Textarea('feedback_message_reply_text', \Orange\Portal\Core\App\Lang::t('feedback_message_reply_text')))->requireField());

		$this->addField((new Submit('feedback_message_reply_submit', \Orange\Portal\Core\App\Lang::t('ADMIN_SAVE'))), 'top');

		$this->enableXSRFProtection();

	}

}