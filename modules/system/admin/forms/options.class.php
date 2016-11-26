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

		$this->addField((new Html('<h3>' . \Orange\Portal\Core\App\Lang::t('ADMIN_GENERAL') . '</h3>')));
		$this->addField((new Text('system_sitename', \Orange\Portal\Core\App\Lang::t('INSTALL_SITENAME'))));
		$this->addField((new Text('system_copyright', \Orange\Portal\Core\App\Lang::t('INSTALL_COPYRIGHT'))));

		$this->addField((new Select('system_theme', \Orange\Portal\Core\App\Lang::t('INSTALL_THEME')))
			->setOptions(\Orange\Portal\Core\App\Theme::getAvailableThemes('name'))
			->setDefault('default')
			->requireField()
		);

		$this->addField((new Email('system_email_public', \Orange\Portal\Core\App\Lang::t('INSTALL_EMAIL_PUBLIC')))->requireField());
		$this->addField((new Email('system_email_system', \Orange\Portal\Core\App\Lang::t('INSTALL_EMAIL_SYSTEM'))));

		$this->addField((new Select('system_timezone', \Orange\Portal\Core\App\Lang::t('INSTALL_TIMEZONE')))
			->setOptions($timezones)
		);
		$this->addField((new Text('system_secretkey', \Orange\Portal\Core\App\Lang::t('OPT_system_secretkey'))));

		$this->addField((new Html('<h3>' . \Orange\Portal\Core\App\Lang::t('ADMIN_LANGUAGE') . '</h3>')));

		$languages = \Orange\Portal\Core\App\Lang::langs();
		$this->addField((new Select('system_default_lang', \Orange\Portal\Core\App\Lang::t('INSTALL_LANGUAGE_DEF')))
			->setOptions($languages)
			->setDefault('en')
			->requireField()
		);
		$this->addField((new Select('system_enabled_langs', \Orange\Portal\Core\App\Lang::t('INSTALL_LANGUAGES')))
			->setOptions($languages)
			->setMultiple()
			->requireField()
		);

		$this->addField((new Html('<h3>' . \Orange\Portal\Core\App\Lang::t('ADMIN_CACHING') . '</h3>')));
		$this->addField((new Checkbox('system_cache_css', \Orange\Portal\Core\App\Lang::t('OPT_system_cache_css')))->setDefault(1));
		$this->addField((new Checkbox('system_cache_js', \Orange\Portal\Core\App\Lang::t('OPT_system_cache_js')))->setDefault(1));

		$this->addField(new Submit('content_edit_submit', \Orange\Portal\Core\App\Lang::t('ADMIN_SAVE')), 'top');

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
		return $this->values;
	}


}