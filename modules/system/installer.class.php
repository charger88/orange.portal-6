<?php

class OPMI_System extends \Orange\Portal\Core\App\Installer
{

	protected $params = [
		'cache_css' => 0,
		'cache_js' => 0,
	];

	public function installModule($params)
	{
	    \Orange\Portal\Core\App\Portal::$sitecode = $params['sitecode'];
		$configname = isset($params['configname']) ? $params['configname'] : 'default.php';
		$this->params = array_merge($this->params, $params);
		$this->errors = [];
		if (empty($this->errors)) {
			$this->createConfigFile($configname);
		}
		if (empty($this->errors)) {
			$this->createTables([
				'\Orange\Portal\Core\Model\ContentType',
				'\Orange\Portal\Core\Model\ContentText',
				'\Orange\Portal\Core\Model\ContentField',
				'\Orange\Portal\Core\Model\ContentTag',
				'\Orange\Portal\Core\Model\Content',
				'OPMM_System_Media',
				'\Orange\Portal\Core\Model\Privilege',
				'\Orange\Portal\Core\Model\UserGroup',
				'\Orange\Portal\Core\Model\User',
				'\Orange\Portal\Core\Model\Log',
				'\Orange\Portal\Core\Model\Module',
				'\Orange\Portal\Core\Model\Config',
			]);
		}
		if (empty($this->errors)) {
			$this->params['secretkey'] = md5(rand() . rand() . rand() . rand() . time());
			$this->createConfig([
				'sitename' => 'STRING',
				'domain' => 'STRING',
				'base_dir' => 'STRING',
				'copyright' => 'STRING',
				'theme' => 'STRING',
				'default_lang' => 'STRING',
				'secretkey' => 'STRING',
				'enabled_langs' => 'ARRAY',
				'email_public' => 'STRING',
				'email_system' => 'STRING',
				'timezone' => 'STRING',
				'proxy_ip' => 'ARRAY',
				'cache_css' => 'BOOLEAN',
				'cache_js' => 'BOOLEAN',
			]);
		}
		if (empty($this->errors)) {
			$this->createThisModule();
		}
		if (empty($this->errors)) {
			$this->createAdminUser();
		}
		if (empty($this->errors)) {
			$this->createContentTypes();
		}
		if (empty($this->errors)) {
			$this->createContent();
		}
		if (empty($this->errors)) {
			$this->createPrivileges();
		}
		if (empty($this->errors)) {
			$this->createFiles();
		}
		if (!empty($this->errors)) {
			$site_config_dir = new \Orange\FS\Dir('sites', $configname);
			if ($site_config_dir->exists()) {
				$site_config_dir->remove();
			}
			$site_config_dir = new \Orange\FS\Dir('sites');
			if ($site_config_dir->exists()) {
				if (empty($site_config_dir->readDir())) {
					$site_config_dir->remove();
				}
			}
		}
		return $this->errors;
	}

	private function createConfigFile($configname)
	{
		$config['db'] = [
			'master' => [
				'driver' => $this->params['db_type'],
				'server' => $this->params['db_server'],
				'database' => $this->params['db_name'],
				'port' => $this->params['db_port'],
				'user' => $this->params['db_user'],
				'password' => $this->params['db_password'],
				'charset' => 'utf8',
				'collation' => 'utf8_general_ci',
				'prefix' => $this->params['db_prefix']
			]
		];
		$connection = new \Orange\Database\Connection($config['db']['master']);
		try {
			if (!empty($config['db_debug'])) {
				$connection->logfile = OP_SYS_ROOT . 'database.log';
			}
			$connection->driver->connect();
			$php_code = '<' . '?' . 'php'
				. "\n" . '$config[\'db\'] = ['
				. "\n\t" . '\'master\' => ['
				. "\n\t\t" . '\'driver\' => \'' . $this->params['db_type'] . '\','
				. "\n\t\t" . '\'server\' => \'' . $this->params['db_server'] . '\','
				. "\n\t\t" . '\'database\' => \'' . $this->params['db_name'] . '\','
				. "\n\t\t" . '\'port\' => \'' . $this->params['db_port'] . '\','
				. "\n\t\t" . '\'user\' => \'' . $this->params['db_user'] . '\','
				. "\n\t\t" . '\'password\' => \'' . $this->params['db_password'] . '\','
				. "\n\t\t" . '\'charset\' => \'utf8\','
				. "\n\t\t" . '\'collation\' => \'utf8_general_ci\','
				. "\n\t\t" . '\'prefix\' => \'' . $this->params['db_prefix'] . '\''
				. "\n\t" . ']'
				. "\n" . '];';
			$dir = new \Orange\FS\Dir('sites/' . \Orange\Portal\Core\App\Portal::$sitecode . '/config');
			$file = new \Orange\FS\File($dir, $configname);
			if (!($result = $file->save($php_code))) {
				$this->errors['db_prefix'] = 'Config file was not saved';
			}
		} catch (\Orange\Database\DBException $e) {
			$this->errors['db_server'] = 'Server, port, username or password is wrong, or database is not exists.';
			$result = false;
		}
		return $result;
	}

	protected function createThisModule()
	{
		$id = parent::createThisModule();
		if ($id !== 1) {
			$this->errors['db_prefix'] = $id > 0 ? 'Modules table is not empty.' : 'System module was not installed.';
		}
		return $id;
	}

	private function createAdminUser()
	{
		$ug = new \Orange\Portal\Core\Model\UserGroup();
		$ug->setData([
			'group_name' => 'USER_GROUP_ADMIN',
			'group_description' => 'USER_GROUP_ADMIN_DESCRIPTION',
			'group_module' => 'system',
		]);
		$ug->save();
		$ug = new \Orange\Portal\Core\Model\UserGroup();
		$ug->setData([
			'group_name' => 'USER_GROUP_MANAGER',
			'group_description' => 'USER_GROUP_MANAGER_DESCRIPTION',
			'group_module' => 'system',
		]);
		$ug->save();
		$ug = new \Orange\Portal\Core\Model\UserGroup();
		$ug->setData([
			'group_name' => 'USER_GROUP_USER',
			'group_description' => 'USER_GROUP_USER_DESCRIPTION',
			'group_module' => 'system',
		]);
		$ug->save();

		$user = new \Orange\Portal\Core\Model\User();
		$user->setData([
			'user_login' => $this->params['admin_username'],
			'user_email' => $this->params['admin_email'],
			'user_status' => 1,
			'user_groups' => [\Orange\Portal\Core\Model\User::GROUP_ADMIN, \Orange\Portal\Core\Model\User::GROUP_MANAGER, \Orange\Portal\Core\Model\User::GROUP_USER],
			'user_provider' => 0,
			'user_phone' => '',
			'user_name' => $this->params['admin_username'],
		]);
		$user->setPassword($this->params['admin_password']);
		$id = $user->save()->id;
		if ($id !== 1) {
			$result = false;
			$this->errors['db_prefix'] = $id > 0 ? 'Users table is not empty.' : 'Admin user was not created.';
		} else {
			$result = true;
		}
		return $result;
	}

	private function createContentTypes()
	{
		$result = true;
		$content_types_data = [
			[
				'content_type_name' => 'TYPE_ADMIN',
				'content_type_code' => 'admin',
				'content_type_type' => 3,
				'content_type_multilang' => false,
				'content_type_class' => '\Orange\Portal\Core\Model\Admin',
				'content_type_hidden' => ['content_parent_id', 'content_tags', 'content_order', 'content_lang', 'content_area', 'content_slug', 'content_default_lang_id', 'content_on_site_mode', 'content_status', 'content_template', 'content_image', 'content_time_modified', 'content_time_published', 'content_user_id', 'content_commands'],
				'content_type_fields' => [],
				'content_type_texts' => [],
			],
			[
				'content_type_name' => 'TYPE_ERROR',
				'content_type_code' => 'error',
				'content_type_type' => 0,
				'content_type_multilang' => false,
				'content_type_class' => '\Orange\Portal\Core\Model\Error',
				'content_type_hidden' => [],
				'content_type_fields' => [],
				'content_type_texts' => [],
			],
			[
				'content_type_name' => 'TYPE_PAGE',
				'content_type_code' => 'page',
				'content_type_type' => 1,
				'content_type_multilang' => true,
				'content_type_class' => '\Orange\Portal\Core\Model\Page',
				'content_type_hidden' => ['content_type', 'content_area', 'content_order',],
				'content_type_fields' => [
					'seo_title' => ['type' => 'TEXT', 'group' => 'SEO', 'title' => 'CONTENT_FIELD_SEO_TITLE'],
					'seo_description' => ['type' => 'TEXT', 'group' => 'SEO', 'title' => 'CONTENT_FIELD_SEO_DESCRIPTION'],
					'seo_keywords' => ['type' => 'TEXT', 'group' => 'SEO', 'title' => 'CONTENT_FIELD_SEO_KEYWORDS'],
					'seo_canonical' => ['type' => 'TEXT', 'group' => 'SEO', 'title' => 'CONTENT_FIELD_SEO_CANONICAL'],
					'seo_sitemap_priority' => ['type' => 'INTEGER', 'group' => 'SEO', 'title' => 'CONTENT_FIELD_SEO_SITEMAP_PRIORITY'],
					'seo_hidden' => ['type' => 'BOOLEAN', 'group' => 'SEO', 'title' => 'CONTENT_FIELD_SEO_HIDDEN'],
				],
				'content_type_texts' => ['text' => 'ADMIN_CONTENT_TEXT_CONTENT'],
				'content_type_sitemap_priority' => 75,
			],
			[
				'content_type_name' => 'TYPE_BLOCK',
				'content_type_code' => 'block',
				'content_type_type' => 2,
				'content_type_multilang' => true,
				'content_type_class' => '\Orange\Portal\Core\Model\Block',
				'content_type_hidden' => ['content_type', 'content_tags', 'content_time_published', 'content_order', 'content_image', 'content_parent_id'],
				'content_type_fields' => [],
				'content_type_texts' => ['text' => 'ADMIN_CONTENT_TEXT_CONTENT'],
			],
		];
		foreach ($content_types_data as $data) {
			$content = new \Orange\Portal\Core\Model\ContentType();
			$content
				->setData($data)
				->set('content_type_status', 1);
			$results[] = $content->save()->id;
		}
		return $result;
	}

	private function createContent()
	{
		$result = true;
		$content_data = [
			[
				'content_type' => 'page',
				'content_title' => \Orange\Portal\Core\App\Lang::t('INSTALL_HOMEPAGE'),
				'content_access_groups' => [0],
				'content_lang' => \Orange\Portal\Core\App\Portal::$sitelang,
				'content_slug' => 'homepage',
				'content_on_site_mode' => 3,
				'content_status' => 7,
				'content_commands' => [['module' => 'system', 'controller' => 'text', 'method' => 'index', 'static' => false, 'args' => []],],
				'content_template' => 'main-html.phtml',
			],
			[
				'content_type' => 'block',
				'content_title' => \Orange\Portal\Core\App\Lang::t('Site name'),
				'content_access_groups' => [0],
				'content_lang' => '',
				'content_area' => 'header',
				'content_slug' => 'sitename',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'text', 'method' => 'index', 'static' => true, 'args' => ['prefix' => 'logo',]],],
				'content_template' => 'block-element.phtml',
			],
			[
				'content_type' => 'block',
				'content_title' => \Orange\Portal\Core\App\Lang::t('Slogan'),
				'content_access_groups' => [0],
				'content_lang' => '',
				'content_area' => 'header',
				'content_slug' => 'slogan',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'text', 'method' => 'index', 'static' => true, 'args' => []],],
				'content_template' => 'block-element.phtml',
			],
			[
				'content_type' => 'block',
				'content_title' => \Orange\Portal\Core\App\Lang::t('Lang switcher'),
				'content_access_groups' => [0],
				'content_lang' => '',
				'content_area' => 'sub-header',
				'content_slug' => 'lang-switcher',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'system', 'method' => 'langswitcher', 'static' => true, 'args' => []],],
				'content_template' => 'block-element.phtml',
			],
			[
				'content_type' => 'block',
				'content_title' => \Orange\Portal\Core\App\Lang::t('Menu'),
				'content_access_groups' => [0],
				'content_lang' => '',
				'content_area' => 'sub-header',
				'content_slug' => 'main-menu',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'menu', 'method' => 'tree', 'static' => true, 'args' => ['levels' => 1]],],
				'content_template' => 'block-element.phtml',
			],
			[
				'content_type' => 'block',
				'content_title' => \Orange\Portal\Core\App\Lang::t('Search'),
				'content_access_groups' => [0],
				'content_lang' => '',
				'content_area' => 'footer',
				'content_slug' => 'search-form',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'search', 'method' => 'form', 'static' => true, 'args' => []],],
				'content_template' => 'block-element.phtml',
			],
			[
				'content_type' => 'block',
				'content_title' => \Orange\Portal\Core\App\Lang::t('Copytights'),
				'content_access_groups' => [0],
				'content_lang' => '',
				'content_area' => 'footer',
				'content_slug' => 'copyrights',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'system', 'method' => 'copyrights', 'static' => true, 'args' => ['year_opened' => date('Y'), 'powered_by' => true]],],
				'content_template' => 'block-element.phtml',
			],
			[
				'content_type' => 'block',
				'content_title' => \Orange\Portal\Core\App\Lang::t('Admin bar'),
				'content_access_groups' => [1, 2],
				'content_lang' => '',
				'content_area' => 'footer',
				'content_slug' => 'admin-bar',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'system', 'method' => 'adminbar', 'static' => true, 'args' => []],],
				'content_template' => 'block-element.phtml',
			],
			[
				'content_type' => 'error',
				'content_title' => 'ERROR_UNAUTHORIZED',
				'content_access_groups' => [0],
				'content_lang' => '',
				'content_slug' => 'error/unauthorized',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'error', 'method' => 'unauthorized', 'static' => false, 'args' => []],],
				'content_template' => 'main-error.phtml',
			],
			[
				'content_type' => 'error',
				'content_title' => 'ERROR_NOT_FOUND',
				'content_access_groups' => [0],
				'content_lang' => '',
				'content_slug' => 'error/not-found',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'error', 'method' => 'notfound', 'static' => false, 'args' => []],],
				'content_template' => 'main-error.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_CENTER_TITLE',
				'content_access_groups' => [1, 2],
				'content_lang' => '',
				'content_slug' => 'admin/center',
				'content_on_site_mode' => 0,
				'content_status' => 7,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-center', 'method' => '', 'static' => false, 'args' => []],
				],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_MEDIA',
				'content_access_groups' => [1, 2],
				'content_lang' => '',
				'content_slug' => 'admin/media',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-media', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_PAGES',
				'content_access_groups' => [1, 2],
				'content_lang' => '',
				'content_slug' => 'admin/pages',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-pages', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_BLOCKS',
				'content_access_groups' => [1, 2],
				'content_lang' => '',
				'content_slug' => 'admin/blocks',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-blocks', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_TYPES',
				'content_access_groups' => [1],
				'content_lang' => '',
				'content_slug' => 'admin/types',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-types', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_OPTIONS',
				'content_access_groups' => [1],
				'content_lang' => '',
				'content_slug' => 'admin/options',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-options', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_USERS',
				'content_access_groups' => [1],
				'content_lang' => '',
				'content_slug' => 'admin/users',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-users', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_LOGS',
				'content_access_groups' => [1],
				'content_lang' => '',
				'content_slug' => 'admin/log',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-log', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_CACHE',
				'content_access_groups' => [1],
				'content_lang' => '',
				'content_slug' => 'admin/cache',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-cache', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_SITEMAP',
				'content_access_groups' => [1],
				'content_lang' => '',
				'content_slug' => 'admin/sitemap',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-sitemap', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_CONTENT',
				'content_access_groups' => [1, 2],
				'content_lang' => '',
				'content_slug' => 'admin/content',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-content', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_ADMIN_PAGES',
				'content_access_groups' => [1, 2],
				'content_lang' => '',
				'content_slug' => 'admin/panel',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-admin', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_MODULES',
				'content_access_groups' => [1],
				'content_lang' => '',
				'content_slug' => 'admin/modules',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-modules', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_GROUPS',
				'content_access_groups' => [1],
				'content_lang' => '',
				'content_slug' => 'admin/groups',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-groups', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_PRIVILEGES',
				'content_access_groups' => [1],
				'content_lang' => '',
				'content_slug' => 'admin/privileges',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-privileges', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
			[
				'content_type' => 'admin',
				'content_title' => 'ADMIN_FILES',
				'content_access_groups' => [1, 2],
				'content_lang' => '',
				'content_slug' => 'admin/files',
				'content_on_site_mode' => 0,
				'content_status' => 6,
				'content_commands' => [['module' => 'system', 'controller' => 'admin-files', 'method' => '', 'static' => false, 'args' => []],],
				'content_template' => 'main-admin.phtml',
			],
		];
		foreach ($content_data as $data) {
			if ($data['content_type'] === 'page'){
				$content = new \Orange\Portal\Core\Model\Page();
			} else if ($data['content_type'] === 'block'){
				$content = new \Orange\Portal\Core\Model\Block();
			} else if ($data['content_type'] === 'admin'){
				$content = new \Orange\Portal\Core\Model\Admin();
			} else {
				$content = new \Orange\Portal\Core\Model\Content();
			}
			$content->setData($data);
			$content->set('content_time_published', time());
			$content->set('content_user_id', 1);
			$id = $content->save()->id;
			if (!$id) {
				$result = false;
			}
		}
		$text = new \Orange\Portal\Core\Model\ContentText();
		$text->set('content_id', 1);
		$text->set('content_text_role', 'text');
		$text->set('content_text_value', \Orange\Portal\Core\App\Lang::t('INSTALL_HOMEPAGE_TEXT_1'));
		$text->save();
		$text = new \Orange\Portal\Core\Model\ContentText();
		$text->set('content_id', 2);
		$text->set('content_text_role', 'text');
		$text->set('content_text_value', '<a href="' . '%%url%%' . '">' . '%%sitename%%' . '</a>');
		$text->save();
		$text = new \Orange\Portal\Core\Model\ContentText();
		$text->set('content_id', 3);
		$text->set('content_text_role', 'text');
		$text->set('content_text_value', '<p>' . \Orange\Portal\Core\App\Lang::t('INSTALL_HOMEPAGE_TEXT_2') . '</p><p>' . \Orange\Portal\Core\App\Lang::t('INSTALL_HOMEPAGE_TEXT_3') . ' <a href="http://orange-portal.org">orange-portal.org</a>' . '.</p>');
		$text->save();
		return $result;
	}

	private function createPrivileges()
	{
		$priv = new \Orange\Portal\Core\Model\Privilege();
		$priv->set('privilege_name', 'METHOD_SYSTEM_SEARCH_RESULTS');
		$priv->set('user_group_id', \Orange\Portal\Core\Model\User::GROUP_EVERYBODY);
		$priv->save();
	}

	private function createFiles()
	{
		$root_path = 'sites/' . \Orange\Portal\Core\App\Portal::$sitecode . '/static/root';
		$file = new \Orange\FS\File($root_path, 'robots.txt');
		$file->save("User-agent: *\nAllow: /");
		$file = new \Orange\FS\File($root_path, 'favicon.ico');
		$file->save(base64_decode('AAABAAMAMDACAAEAAQAwAwAANgAAACAgAgABAAEAMAEAAGYDAAAQEAIAAQABALAAAACWBAAAKAAAADAAAABgAAAAAQABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'));
		$file = new \Orange\FS\File($root_path, 'sitemap.xml');
		$file->save((new \Orange\Sitemap\Index())->build());
	}

}