<?php

use \Orange\Forms\Form;
use \Orange\Forms\Fields\Inputs\Text;
use \Orange\Forms\Fields\Inputs\Email;
use \Orange\Forms\Fields\Inputs\Number;
use \Orange\Forms\Fields\Inputs\Hidden;
use \Orange\Forms\Fields\Html;
use \Orange\Forms\Fields\Selectors\Select;
use \Orange\Forms\Fields\Buttons\Submit;

class OPMX_System_Install extends Form {

	protected function init($params){

        $timezones = timezone_identifiers_list();
        $timezones = array_values($timezones);
        $timezones = array_combine($timezones,$timezones);

		$this->addField((new Html('<h3>'.OPAL_Lang::t('INSTALL_DB').'</h3>')), 'step-1');
		$this->addField((new Text('db_server', OPAL_Lang::t('INSTALL_DB_SERVER')))->setDefault('localhost')->requireField(), 'step-1');
		$this->addField((new Number('db_port', OPAL_Lang::t('INSTALL_DB_PORT')))->setDefault('3306'), 'step-1');
		$this->addField((new Text('db_name', OPAL_Lang::t('INSTALL_DB_NAME')))->requireField(), 'step-1');
		$this->addField((new Text('db_prefix', OPAL_Lang::t('INSTALL_DB_PREFIX')))->setDefault('op6_'), 'step-1');
		$this->addField((new Text('db_user', OPAL_Lang::t('INSTALL_DB_USER')))->requireField(), 'step-1');
		$this->addField((new Text('db_password', OPAL_Lang::t('INSTALL_DB_PASSWORD'))), 'step-1');
		$this->addField((new Select('db_type', OPAL_Lang::t('INSTALL_DB_DRIVER')))
            ->setOptions($this->getDrivers())
            ->setDefault('MySQL')
            ->requireField(), 'step-1');

        $this->addField((new Html('<h3>'.OPAL_Lang::t('INSTALL_SITE').'</h3>')), 'step-2');
        $this->addField((new Text('sitename', OPAL_Lang::t('INSTALL_SITENAME')))->requireField(), 'step-2');
        $this->addField((new Text('sitecode', OPAL_Lang::t('INSTALL_SITECODE')))->requireField(), 'step-2');
        $this->addField((new Text('copyright', OPAL_Lang::t('INSTALL_COPYRIGHT'))), 'step-2');
        $this->addField((new Select('timezone', OPAL_Lang::t('INSTALL_TIMEZONE')))
            ->setOptions($timezones), 'step-2');
        $this->addField((new Select('theme', OPAL_Lang::t('INSTALL_THEME')))
            ->setOptions(OPAL_Theme::getAvailableThemes('name'))
            ->setDefault('orange2016')
            ->requireField(), 'step-2');

        $this->addField((new Html('<h3>'.OPAL_Lang::t('INSTALL_LANGUAGE').'</h3>')), 'step-3');
        $languages = OPAL_Lang::langs();
		$this->addField((new Select('default_lang', OPAL_Lang::t('INSTALL_LANGUAGE_DEF')))
            ->setOptions($languages)
            ->setDefault('en')
            ->requireField(), 'step-3');
		$this->addField((new Select('enabled_langs', OPAL_Lang::t('INSTALL_LANGUAGES')))
            ->setOptions($languages)
            ->setMultiple()
            ->setDefault('en')
            ->requireField(), 'step-3');

        $this->addField((new Html('<h3>'.OPAL_Lang::t('INSTALL_ADMIN').'</h3>')), 'step-4');
		$this->addField((new Text('admin_username', OPAL_Lang::t('INSTALL_ADMIN_USERNAME')))->requireField()->setDefault('admin'), 'step-4');
		$this->addField((new Text('admin_password', OPAL_Lang::t('INSTALL_ADMIN_PASSWORD')))->requireField()->setDefault(''), 'step-4');
		$this->addField((new Text('admin_email', OPAL_Lang::t('INSTALL_ADMIN_EMAIL')))->requireField(), 'step-4');

        $this->addField((new Html('<h3>'.OPAL_Lang::t('INSTALL_EMAIL').'</h3>')), 'step-5');
		$this->addField((new Email('email_public', OPAL_Lang::t('INSTALL_EMAIL_PUBLIC')))->requireField(), 'step-5');
		$this->addField((new Email('email_system', OPAL_Lang::t('INSTALL_EMAIL_SYSTEM')))->requireField(), 'step-5');
				
		$this->addField((new Hidden('step'))->setDefault('step-last'), 'step-last');
		$this->addField((new Submit('go', OPAL_Lang::t('INSTALL'))), 'step-last');
				
	}

    protected function getDrivers(){
        $drivers = (new \Orange\FS\Dir('vendor/charger88/orange.database/src/Orange/Database/Drivers'))->readDir();
        $drivers = array_map(function($val){
            $val = explode('.', $val->getName());
            return $val[0];
        }, $drivers);
        $drivers = array_combine($drivers, $drivers);
        unset($drivers['Driver']);
        return $drivers;
    }

}