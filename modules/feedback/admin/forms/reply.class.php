<?php

class OPMX_Feedback_Reply extends OPAL_Form {
		
	protected function build($params){

        $this->addField('feedback_message_reply_from_name', 'text', OPAL_Lang::t('feedback_message_reply_from_name'), ['required' => true]);
        $this->addField('feedback_message_reply_from_email', 'text', OPAL_Lang::t('feedback_message_reply_from_email'), ['required' => true]);
        $this->addField('feedback_message_reply_text', 'textarea', OPAL_Lang::t('feedback_message_reply_text'), ['required' => true]);

        $this->addField('feedback_message_reply_submit', 'submit', OPAL_Lang::t('ADMIN_SAVE'), array(), 'buttons');

	}
		
}