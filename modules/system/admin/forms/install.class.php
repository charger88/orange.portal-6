<?php

class OPMX_System_Install extends OPAL_Form {
	
	protected function build($params){

		$this->addHTML('<h3>'.OPAL_Lang::t('INSTALL_DB').'</h3>', 'step-1');
		$this->addField('db_server', 'text', OPAL_Lang::t('INSTALL_DB_SERVER'), array('value' => 'localhost','required' => 'required'), 'step-1');
		$this->addField('db_port', 'number', OPAL_Lang::t('INSTALL_DB_PORT'), array('value' => '3306'), 'step-1');
		$this->addField('db_name', 'text', OPAL_Lang::t('INSTALL_DB_NAME'), array('required' => 'required'), 'step-1');
		$this->addField('db_prefix', 'text', OPAL_Lang::t('INSTALL_DB_PREFIX'), array('value' => 'op6_'), 'step-1');
		$this->addField('db_user', 'text', OPAL_Lang::t('INSTALL_DB_USER'), array('required' => 'required'), 'step-1');
		$this->addField('db_password', 'text', OPAL_Lang::t('INSTALL_DB_PASSWORD'), array(), 'step-1');
		$this->addField('db_type', 'select', OPAL_Lang::t('INSTALL_DB_DRIVER'), array('options' => array('MySQL' => 'MySQL'), 'value' => 'mi', 'required' => 'required'), 'step-1');
		
		$this->addHTML('<h3>'.OPAL_Lang::t('INSTALL_SITE').'</h3>', 'step-2');
		$this->addField('sitename', 'text', OPAL_Lang::t('INSTALL_SITENAME'), array(), 'step-2');
		$this->addField('copyright', 'text', OPAL_Lang::t('INSTALL_COPYRIGHT'), array(), 'step-2');
		$this->addField('theme', 'select', OPAL_Lang::t('INSTALL_THEME'), array('options' => OPAL_Theme::getAvalibleThemes('name'), 'value' => 'default', 'required' => 'required'), 'step-2'); //TODO Get DB drivers
		
		$this->addHTML('<h3>'.OPAL_Lang::t('INSTALL_LANGUAGE').'</h3>', 'step-3');
		$languages = OPAL_Lang::langs();
		$this->addField('default_lang', 'select', OPAL_Lang::t('INSTALL_LANGUAGE_DEF'), array('options' => $languages, 'value' => 'en', 'required' => 'required'), 'step-3');
		$this->addField('enabled_langs', 'select', OPAL_Lang::t('INSTALL_LANGUAGES'), array('options' => $languages, 'multiple' => 'multiple', 'value' => 'en', 'required' => 'required'), 'step-3');
				
		$this->addHTML('<h3>'.OPAL_Lang::t('INSTALL_ADMIN').'</h3>', 'step-4');
		$this->addField('admin_username', 'text', OPAL_Lang::t('INSTALL_ADMIN_USERNAME'), array('value' => 'admin','required' => 'required'), 'step-4');
		$this->addField('admin_password', 'text', OPAL_Lang::t('INSTALL_ADMIN_PASSWORD'), array('value' => '','required' => 'required'), 'step-4');
		$this->addField('admin_email', 'email', OPAL_Lang::t('INSTALL_ADMIN_EMAIL'), array('required' => 'required'), 'step-4');
		
		$this->addHTML('<h3>'.OPAL_Lang::t('INSTALL_EMAIL').'</h3>', 'step-5');
		$this->addField('email_public', 'email', OPAL_Lang::t('INSTALL_EMAIL_PUBLIC'), array('required' => 'required'), 'step-5');
		$this->addField('email_system', 'email', OPAL_Lang::t('INSTALL_EMAIL_SYSTEM'), array(), 'step-5');
				
		$this->addField('step', 'hidden', null, array(), 'step-last');
		$this->addField('go', 'submit', OPAL_Lang::t('INSTALL'), array(), 'step-last');
				
	}
	
}