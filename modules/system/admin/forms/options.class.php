<?php

use Orange\Forms\Fields\Buttons\Submit;
use Orange\Forms\Fields\Html;
use Orange\Forms\Fields\Inputs\Email;
use Orange\Forms\Fields\Inputs\Text;
use Orange\Forms\Fields\Selectors\Checkbox;
use Orange\Forms\Fields\Selectors\Select;
use Orange\Forms\Form;

class OPMX_System_Options extends Form
{

	protected function init($params)
	{

		$timezones = timezone_identifiers_list();
		$timezones = array_values($timezones);
		$timezones = array_combine($timezones, $timezones);

		$this->addField((new Html('<h3>' . OPAL_Lang::t('ADMIN_GENERAL') . '</h3>')));
		$this->addField((new Text('system_sitename', OPAL_Lang::t('INSTALL_SITENAME'))));
		$this->addField((new Text('system_copyright', OPAL_Lang::t('INSTALL_COPYRIGHT'))));

		$this->addField((new Select('system_theme', OPAL_Lang::t('INSTALL_THEME')))
			->setOptions(OPAL_Theme::getAvailableThemes('name'))
			->setDefault('default')
			->requireField()
		);

		$this->addField((new Email('system_email_public', OPAL_Lang::t('INSTALL_EMAIL_PUBLIC')))->requireField());
		$this->addField((new Email('system_email_system', OPAL_Lang::t('INSTALL_EMAIL_SYSTEM'))));

		$this->addField((new Select('system_timezone', OPAL_Lang::t('INSTALL_TIMEZONE')))
			->setOptions($timezones)
		);
		$this->addField((new Text('system_secretkey', OPAL_Lang::t('OPT_system_secretkey'))));

		$this->addField((new Html('<h3>' . OPAL_Lang::t('ADMIN_LANGUAGE') . '</h3>')));

		$languages = OPAL_Lang::langs();
		$this->addField((new Select('system_default_lang', OPAL_Lang::t('INSTALL_LANGUAGE_DEF')))
			->setOptions($languages)
			->setDefault('en')
			->requireField()
		);
		$this->addField((new Select('system_enabled_langs', OPAL_Lang::t('INSTALL_LANGUAGES')))
			->setOptions($languages)
			->setMultiple()
			->requireField()
		);

		$this->addField((new Html('<h3>' . OPAL_Lang::t('ADMIN_CACHING') . '</h3>')));
		$this->addField((new Checkbox('system_cache_css', OPAL_Lang::t('OPT_system_cache_css')))->setDefault(1));
		$this->addField((new Checkbox('system_cache_js', OPAL_Lang::t('OPT_system_cache_js')))->setDefault(1));
		$this->addField((new Checkbox('system_cache_method', OPAL_Lang::t('OPT_system_cache_method')))->setDefault(1));

		$this->addField(new Submit('content_edit_submit', OPAL_Lang::t('ADMIN_SAVE')), 'top');

		$this->enableXSRFProtection();

	}

	public function getValues()
	{
		if (empty($this->values['system_cache_css'])){
			$this->values['system_cache_css'] = 0;
		}
		if (empty($this->values['system_cache_js'])){
			$this->values['system_cache_js'] = 0;
		}
		if (empty($this->values['system_cache_method'])){
			$this->values['system_cache_method'] = 0;
		}
		return $this->values;
	}


}