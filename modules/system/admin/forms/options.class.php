<?php

class OPMX_System_Options extends OPAL_Form {
		
	protected function build($params){

		$this->addHTML('<h3>'.OPAL_Lang::t('ADMIN_GENERAL').'</h3>');
		$this->addField('system_sitename', 'text', OPAL_Lang::t('INSTALL_SITENAME'), array());
		$this->addField('system_copyright', 'text', OPAL_Lang::t('INSTALL_COPYRIGHT'), array());
		$this->addField('system_theme', 'select', OPAL_Lang::t('INSTALL_THEME'), array('options' => OPAL_Theme::getAvalibleThemes('name'), 'value' => 'default', 'required' => 'required'));
		$this->addField('system_email_public', 'email', OPAL_Lang::t('INSTALL_EMAIL_PUBLIC'), array('required' => 'required'));
		$this->addField('system_email_system', 'email', OPAL_Lang::t('INSTALL_EMAIL_SYSTEM'), array());
		$this->addField('system_secretkey', 'text', OPAL_Lang::t('OPT_system_secretkey'), array());

		$this->addHTML('<h3>'.OPAL_Lang::t('ADMIN_LANGUAGE').'</h3>');
		$languages = OPAL_Lang::langs();
		$this->addField('system_default_lang', 'select', OPAL_Lang::t('INSTALL_LANGUAGE_DEF'), array('options' => $languages, 'value' => 'en', 'required' => 'required'));
		$this->addField('system_enabled_langs', 'select', OPAL_Lang::t('INSTALL_LANGUAGES'), array('options' => $languages, 'multiple' => 'multiple', 'required' => 'required'));
		
		$this->addHTML('<h3>'.OPAL_Lang::t('ADMIN_CACHING').'</h3>');
		$this->addField('system_cache_css', 'checkbox', OPAL_Lang::t('OPT_system_cache_css'), array('value' => 1));
		$this->addField('system_cache_js', 'checkbox', OPAL_Lang::t('OPT_system_cache_js'), array('value' => 1));
		$this->addField('system_cache_method', 'checkbox', OPAL_Lang::t('OPT_system_cache_method'), array('value' => 1));

        $this->addField('content_edit_submit', 'submit', OPAL_Lang::t('ADMIN_SAVE'), array(), 'buttons');
		
	}
	
	
}