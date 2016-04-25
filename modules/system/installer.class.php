<?php

class OPMI_System extends OPAL_Installer {

	protected $params = array(
		'cache_css'    => 0,
		'cache_js'     => 0,
		'cache_method' => 0,
	);
	
	public function installModule($params){
        OPAL_Portal::$sitecode = $params['sitecode'];
		$configname = isset($params['configname']) ? $params['configname'] : 'default.php';
		$this->params = array_merge($this->params,$params);
		$this->errors = array();
		if (empty($this->errors)){
			$this->createConfigFile($configname);
		}
		if (empty($this->errors)){
			$this->createTables(array(
				'OPAM_Content_Type',
				'OPAM_Content_Text',
                'OPAM_Content_Field',
                'OPAM_Content_Tag',
				'OPAM_Content',
				'OPMM_System_Media',
				'OPAM_Privilege',
				'OPAM_User_Group',
				'OPAM_User',
                'OPAM_Log',
				'OPAM_Module',
				'OPAM_Config',
			));
		}
		if (empty($this->errors)){
            $this->params['secretkey'] = md5(rand().rand().rand().rand().time());
			$this->createConfig(array(
				'sitename'      => 'STRING',
				'domain'        => 'STRING',
				'base_dir'      => 'STRING',
				'copyright'     => 'STRING',
				'theme'         => 'STRING',
				'default_lang'  => 'STRING',
                'secretkey'     => 'STRING',
				'enabled_langs' => 'ARRAY',
				'email_public'  => 'STRING',
				'email_system'  => 'STRING',
				'proxy_ip'      => 'ARRAY',
				'cache_css'     => 'BOOLEAN',
				'cache_js'      => 'BOOLEAN',
				'cache_method'  => 'BOOLEAN',
			));
		}
		if (empty($this->errors)){
			$this->createThisModule();
		}
		if (empty($this->errors)){
			$this->createAdminUser();
		}
		if (empty($this->errors)){
			$this->createContentTypes();
		}
		if (empty($this->errors)){
			$this->createContent();
		}
        if (empty($this->errors)){
            $this->createPrivileges();
        }
        if (empty($this->errors)){
            $this->createFiles();
        }
        if (!empty($this->errors)){
            $site_config_dir = new OPAL_File($configname,'sites');
            $site_config_dir->delete();
            $site_config_dir = new OPAL_File('sites');
            if (!$site_config_dir->dirFiles()){
                $site_config_dir->delete();
            }
        }
		return $this->errors;
	}
	
	private function createConfigFile($configname){
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
                'table_prefix' => $this->params['db_prefix']
            ]
        ];
        $connection = new \Orange\Database\Connection($config['db']['master']);
        try {
            if (!empty($config['db_debug'])) {
                $connection->logfile = OP_SYS_ROOT . 'database.log';
            }
            $connection->driver->connect();
            $php_code = '<'.'?'.'php'
                ."\n".'$config[\'db\'] = ['
                ."\n\t".'\'master\' => ['
                ."\n\t\t".'\'driver\' => \''.$this->params['db_type'].'\','
                ."\n\t\t".'\'server\' => \''.$this->params['db_server'].'\','
                ."\n\t\t".'\'database\' => \''.$this->params['db_name'].'\','
                ."\n\t\t".'\'port\' => \''.$this->params['db_port'].'\','
                ."\n\t\t".'\'user\' => \''.$this->params['db_user'].'\','
                ."\n\t\t".'\'password\' => \''.$this->params['db_password'].'\','
                ."\n\t\t".'\'charset\' => \'utf8\','
                ."\n\t\t".'\'collation\' => \'utf8_general_ci\','
                ."\n\t\t".'\'table_prefix\' => \''.$this->params['db_prefix'].'\''
                ."\n\t".']'
                ."\n".'];'
            ;
            $file = new OPAL_File($configname,'sites/'.OPAL_Portal::$sitecode.'/config');
            if (!($result = $file->saveData($php_code))){
                $this->errors['db_prefix'] = 'Config file was not saved';
            }
        } catch (\Orange\Database\DBException $e){
            $this->errors['db_server'] = 'Server, port, username or password is wrong, or database is not exists.';
            $result = false;
        }
		return $result;
	}

	protected function createThisModule(){
		$id = parent::createThisModule();
		if ($id !== 1){
			$this->errors['db_prefix'] = $id > 0 ? 'Modules table is not empty.' : 'System module was not installed.';
		}
		return $id;
	}
	
	private function createAdminUser(){
		$ug = new OPAM_User_Group();
		$ug->setData(array(
			'group_name'         => 'USER_GROUP_ADMIN',
			'group_description'  => 'USER_GROUP_ADMIN_DESCRIPTION',
			'group_module'       => 'system',
		));
		$ug->save();
		$ug = new OPAM_User_Group();
		$ug->setData(array(
			'group_name'         => 'USER_GROUP_MANAGER',
			'group_description'  => 'USER_GROUP_MANAGER_DESCRIPTION',
			'group_module'       => 'system',
		));
		$ug->save();
		$ug = new OPAM_User_Group();
		$ug->setData(array(
			'group_name'         => 'USER_GROUP_USER',
			'group_description'  => 'USER_GROUP_USER_DESCRIPTION',
			'group_module'       => 'system',
		));
		$ug->save();
		
		$user = new OPAM_User();
		$user->setData(array(
			'user_login'    => $this->params['admin_username'],
			'user_email'    => $this->params['admin_email'],
			'user_status'   => 1,
			'user_groups'   => array(OPAM_User::GROUP_ADMIN,OPAM_User::GROUP_MANAGER,OPAM_User::GROUP_USER),
			'user_provider' => 0,
			'user_phone'    => '',
			'user_name'     => $this->params['admin_username'],
		));
        $user->setPassword($this->params['admin_password']);
		$id = $user->save()->id;
		if ($id !== 1){
			$result = false;
			$this->errors['db_prefix'] = $id > 0 ? 'Users table is not empty.' : 'Admin user was not created.';
		} else {
            $result = true;
        }
		return $result;
	}
	
	private function createContentTypes(){
		$result = true;
		$content_types_data = array(
			array(
				'content_type_name'       => 'TYPE_ADMIN',
				'content_type_code'       => 'admin',
				'content_type_type'       => 3,
				'content_type_multilang'  => false,
				'content_type_class'      => 'OPAM_Admin',
				'content_type_hidden'     => array('content_parent_id','content_tags','content_order','content_lang','content_area','content_slug','content_default_lang_id','content_on_site_mode','content_status','content_template','content_image','content_time_modified','content_time_published','content_user_id'),
				'content_type_fields'     => array(),
				'content_type_texts'      => array(),
			),
			array(
				'content_type_name'       => 'TYPE_ERROR',
				'content_type_code'       => 'error',
				'content_type_type'       => 0,
				'content_type_multilang'  => false,
				'content_type_class'      => 'OPAM_Error',
				'content_type_hidden'     => array(),
				'content_type_fields'     => array(),
				'content_type_texts'      => array(),
			),
			array(
				'content_type_name'       => 'TYPE_PAGE',
				'content_type_code'       => 'page',
				'content_type_type'       => 1,
				'content_type_multilang'  => true,
				'content_type_class'      => 'OPAM_Page',
				'content_type_hidden'     => array('content_type','content_area','content_order',),
				'content_type_fields'     => array(
					'seo_title'            => array('type' => 'TEXT','group' => 'SEO','title' => 'CONTENT_FIELD_SEO_TITLE'),
					'seo_description'      => array('type' => 'TEXT','group' => 'SEO','title' => 'CONTENT_FIELD_SEO_DESCRIPTION'),
					'seo_keywords'         => array('type' => 'TEXT','group' => 'SEO','title' => 'CONTENT_FIELD_SEO_KEYWORDS'),
					'seo_canonical'        => array('type' => 'TEXT','group' => 'SEO','title' => 'CONTENT_FIELD_SEO_CANONICAL'),
                    'seo_sitemap_priority' => array('type' => 'INTEGER','group' => 'SEO','title' => 'CONTENT_FIELD_SEO_SITEMAP_PRIORITY'),
                    'seo_hidden'           => array('type' => 'BOOLEAN','group' => 'SEO','title' => 'CONTENT_FIELD_SEO_HIDDEN'),
				),
				'content_type_texts'      => array('text' => 'ADMIN_CONTENT_TEXT_CONTENT'),
                'content_type_sitemap_priority' => 75,
			),
			array(
				'content_type_name'       => 'TYPE_BLOCK',
				'content_type_code'       => 'block',
				'content_type_type'       => 2,
				'content_type_multilang'  => true,
				'content_type_class'      => 'OPAM_Block',
				'content_type_hidden'     => array('content_type','content_tags','content_time_published','content_order','content_image'),
				'content_type_fields'     => array(),
				'content_type_texts'      => array('text' => 'ADMIN_CONTENT_TEXT_CONTENT'),
			),
		);
		foreach ($content_types_data as $data){
			$content = new OPAM_Content_Type();
			$content
                ->setData($data)
                ->set('content_type_status', 1)
            ;
			$results[] = $content->save()->id;
		}
		return $result;
	}
	
	private function createContent(){
		$result = true;
		$lang = OPAL_Portal::config('system_default_lang','en');
		$content_data = array(
			array(
				'content_type'           => 'page',
				'content_title'          => OPAL_Lang::t('Homepage'),
				'content_access_groups'  => array(0),
				'content_lang'           => $lang,
				'content_slug'           => 'homepage',
				'content_on_site_mode'   => 3,
				'content_status'         => 7,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'text', 'method' => 'index', 'static' => false, 'args' => array() ), ),
				'content_template'       => 'main-html.phtml',
			),
			array(
				'content_type'           => 'block',
				'content_title'          => OPAL_Lang::t('Site name'),
				'content_access_groups'  => array(0),
				'content_lang'           => '',
				'content_area'           => 'header',
				'content_slug'           => 'sitename',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'text', 'method' => 'index', 'static' => true, 'args' => array( 'prefix' => 'logo', )  ), ),
				'content_template'       => 'block-element.phtml',
			),
			array(
				'content_type'           => 'block',
				'content_title'          => OPAL_Lang::t('Slogan'),
				'content_access_groups'  => array(0),
				'content_lang'           => '',
				'content_area'           => 'top-section',
				'content_slug'           => 'slogan',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'text', 'method' => 'index', 'static' => true, 'args' => array() ), ),
				'content_template'       => 'block-element.phtml',
			),
			array(
				'content_type'           => 'block',
				'content_title'          => OPAL_Lang::t('Lang switcher'),
				'content_access_groups'  => array(0),
				'content_lang'           => '',
				'content_area'           => 'header',
				'content_slug'           => 'lang-switcher',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'system', 'method' => 'langswitcher', 'static' => true, 'args' => array() ), ),
				'content_template'       => 'block-element.phtml',
			),
			array(
				'content_type'           => 'block',
				'content_title'          => OPAL_Lang::t('Menu'),
				'content_access_groups'  => array(0),
				'content_lang'           => '',
				'content_area'           => 'sub-header',
				'content_slug'           => 'main-menu',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'menu', 'method' => 'tree', 'static' => true, 'args' => array() ), ),
				'content_template'       => 'block-element.phtml',
			),
			array(
				'content_type'           => 'block',
				'content_title'          => OPAL_Lang::t('Search'),
				'content_access_groups'  => array(0),
				'content_lang'           => '',
				'content_area'           => 'header',
				'content_slug'           => 'search-form',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'search', 'method' => 'form', 'static' => true, 'args' => array() ), ),
				'content_template'       => 'block-element.phtml',
			),
			array(
				'content_type'           => 'block',
				'content_title'          => OPAL_Lang::t('Copytights'),
				'content_access_groups'  => array(0),
				'content_lang'           => '',
				'content_area'           => 'footer',
				'content_slug'           => 'copyrights',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'system', 'method' => 'copyrights', 'static' => true, 'args' => array('year_opened' => date('Y'), 'powered_by' => true) ), ),
				'content_template'       => 'block-element.phtml',
			),
			array(
				'content_type'           => 'block',
				'content_title'          => OPAL_Lang::t('Admin bar'),
				'content_access_groups'  => array(1,2),
				'content_lang'           => '',
				'content_area'           => 'footer',
				'content_slug'           => 'admin-bar',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'system', 'method' => 'adminbar', 'static' => true, 'args' => array( ) ), ),
				'content_template'       => 'block-element.phtml',
			),
			array(
				'content_type'           => 'error',
				'content_title'          => 'ERROR_UNAUTHORIZED',
				'content_access_groups'  => array(0),
				'content_lang'           => '',
				'content_slug'           => 'error/unauthorized',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'error', 'method' => 'unauthorized', 'static' => false, 'args' => array() ), ),
				'content_template'       => 'main-error.phtml',
			),
			array(
				'content_type'           => 'error',
				'content_title'          => 'ERROR_NOT_FOUND',
				'content_access_groups'  => array(0),
				'content_lang'           => '',
				'content_slug'           => 'error/not-found',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'error', 'method' => 'notfound', 'static' => false, 'args' => array() ), ),
				'content_template'       => 'main-error.phtml',
			),
			array(
				'content_type'           => 'admin',
				'content_title'          => 'ADMIN_CENTER_TITLE',
				'content_access_groups'  => array(1,2),
				'content_lang'           => '',
				'content_slug'           => 'admin/center',
				'content_on_site_mode'   => 0,
				'content_status'         => 7,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-center', 'method' => '', 'static' => false, 'args' => array() ),
				),
				'content_template'       => 'main-admin.phtml',
			),
			array(
				'content_type'           => 'admin',
				'content_title'          => 'ADMIN_MEDIA',
				'content_access_groups'  => array(1,2),
				'content_lang'           => '',
				'content_slug'           => 'admin/media',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-media', 'method' => '', 'static' => false, 'args' => array() ), ),
				'content_template'       => 'main-admin.phtml',
			),
			array(
				'content_type'           => 'admin',
				'content_title'          => 'ADMIN_PAGES',
				'content_access_groups'  => array(1,2),
				'content_lang'           => '',
				'content_slug'           => 'admin/pages',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-pages', 'method' => '', 'static' => false, 'args' => array() ), ),
				'content_template'       => 'main-admin.phtml',
			),
			array(
				'content_type'           => 'admin',
				'content_title'          => 'ADMIN_BLOCKS',
				'content_access_groups'  => array(1,2),
				'content_lang'           => '',
				'content_slug'           => 'admin/blocks',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-blocks', 'method' => '', 'static' => false, 'args' => array() ), ),
				'content_template'       => 'main-admin.phtml',
			),
			array(
				'content_type'           => 'admin',
				'content_title'          => 'ADMIN_TYPES',
				'content_access_groups'  => array(1),
				'content_lang'           => '',
				'content_slug'           => 'admin/types',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-types', 'method' => '', 'static' => false, 'args' => array() ), ),
				'content_template'       => 'main-admin.phtml',
			),
			array(
				'content_type'           => 'admin',
				'content_title'          => 'ADMIN_OPTIONS',
				'content_access_groups'  => array(1),
				'content_lang'           => '',
				'content_slug'           => 'admin/options',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-options', 'method' => '', 'static' => false, 'args' => array() ), ),
				'content_template'       => 'main-admin.phtml',
			),
            array(
                'content_type'           => 'admin',
                'content_title'          => 'ADMIN_USERS',
                'content_access_groups'  => array(1),
                'content_lang'           => '',
                'content_slug'           => 'admin/users',
                'content_on_site_mode'   => 0,
                'content_status'         => 6,
                'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-users', 'method' => '', 'static' => false, 'args' => array() ), ),
                'content_template'       => 'main-admin.phtml',
            ),
			array(
				'content_type'           => 'admin',
				'content_title'          => 'ADMIN_LOGS',
				'content_access_groups'  => array(1),
				'content_lang'           => '',
				'content_slug'           => 'admin/log',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-log', 'method' => '', 'static' => false, 'args' => array() ), ),
				'content_template'       => 'main-admin.phtml',
			),
            array(
                'content_type'           => 'admin',
                'content_title'          => 'ADMIN_CACHE',
                'content_access_groups'  => array(1),
                'content_lang'           => '',
                'content_slug'           => 'admin/cache',
                'content_on_site_mode'   => 0,
                'content_status'         => 6,
                'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-cache', 'method' => '', 'static' => false, 'args' => array() ), ),
                'content_template'       => 'main-admin.phtml',
            ),
            array(
                'content_type'           => 'admin',
                'content_title'          => 'ADMIN_SITEMAP',
                'content_access_groups'  => array(1),
                'content_lang'           => '',
                'content_slug'           => 'admin/sitemap',
                'content_on_site_mode'   => 0,
                'content_status'         => 6,
                'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-sitemap', 'method' => '', 'static' => false, 'args' => array() ), ),
                'content_template'       => 'main-admin.phtml',
            ),
			array(
				'content_type'           => 'admin',
				'content_title'          => 'ADMIN_CONTENT',
				'content_access_groups'  => array(1,2),
				'content_lang'           => '',
				'content_slug'           => 'admin/content',
				'content_on_site_mode'   => 0,
				'content_status'         => 6,
				'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-content', 'method' => '', 'static' => false, 'args' => array() ), ),
				'content_template'       => 'main-admin.phtml',
			),
            array(
                'content_type'           => 'admin',
                'content_title'          => 'ADMIN_ADMIN_PAGES',
                'content_access_groups'  => array(1,2),
                'content_lang'           => '',
                'content_slug'           => 'admin/panel',
                'content_on_site_mode'   => 0,
                'content_status'         => 6,
                'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-admin', 'method' => '', 'static' => false, 'args' => array() ), ),
                'content_template'       => 'main-admin.phtml',
            ),
            array(
                'content_type'           => 'admin',
                'content_title'          => 'ADMIN_MODULES',
                'content_access_groups'  => array(1),
                'content_lang'           => '',
                'content_slug'           => 'admin/modules',
                'content_on_site_mode'   => 0,
                'content_status'         => 6,
                'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-modules', 'method' => '', 'static' => false, 'args' => array() ), ),
                'content_template'       => 'main-admin.phtml',
            ),
            array(
                'content_type'           => 'admin',
                'content_title'          => 'ADMIN_GROUPS',
                'content_access_groups'  => array(1),
                'content_lang'           => '',
                'content_slug'           => 'admin/groups',
                'content_on_site_mode'   => 0,
                'content_status'         => 6,
                'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-groups', 'method' => '', 'static' => false, 'args' => array() ), ),
                'content_template'       => 'main-admin.phtml',
            ),
            array(
                'content_type'           => 'admin',
                'content_title'          => 'ADMIN_PRIVILEGES',
                'content_access_groups'  => array(1),
                'content_lang'           => '',
                'content_slug'           => 'admin/privileges',
                'content_on_site_mode'   => 0,
                'content_status'         => 6,
                'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-privileges', 'method' => '', 'static' => false, 'args' => array() ), ),
                'content_template'       => 'main-admin.phtml',
            ),
            array(
                'content_type'           => 'admin',
                'content_title'          => 'ADMIN_FILES',
                'content_access_groups'  => array(1,2),
                'content_lang'           => '',
                'content_slug'           => 'admin/files',
                'content_on_site_mode'   => 0,
                'content_status'         => 6,
                'content_commands'       => array( array( 'module' => 'system', 'controller' => 'admin-files', 'method' => '', 'static' => false, 'args' => array() ), ),
                'content_template'       => 'main-admin.phtml',
            ),
        );
		foreach ($content_data as $data){
			$content = new OPAM_Content();
			$content->setData($data);
			$content->set('content_time_published', time());
			$content->set('content_user_id', 1);
			$id = $content->save()->id;
			if (!$id){
				$result = false;
			}
		}
		$text = new OPAM_Content_Text();
		$text->set('content_id', 1);
		$text->set('content_text_role', 'text');
		$text->set('content_text_value', OPAL_Lang::t('Hi! We are glad to show you homepage of new portal.'));
		$text->save();
		$text = new OPAM_Content_Text();
		$text->set('content_id', 2);
		$text->set('content_text_role', 'text');
		$text->set('content_text_value', '<a href="'.'%%url%%'.'">'.'%%sitename%%'.'</a>');
		$text->save();
		$text = new OPAM_Content_Text();
		$text->set('content_id', 2);
		$text->set('content_text_role', 'text');
		$text->set('content_text_value', '<span>Right start</span><br/><span>Enjoy!</span>');
		$text->save();
		return $result;
	}

    private function createPrivileges(){
        $priv = new OPAM_Privilege();
        $priv->set('privilege_name','METHOD_SYSTEM_SEARCH_RESULTS');
        $priv->set('user_group_id',OPAM_User::GROUP_EVERYBODY);
        $priv->save();
    }

    private function createFiles(){
        $root_path = 'sites/'.OPAL_Portal::$sitecode.'/static/root';
        $file = new OPAL_File('robots.txt',$root_path);
        $file->saveData("User-agent: *\nAllow: /");
        $file = new OPAL_File('favicon.ico',$root_path);
        $file->saveData(base64_decode('AAABAAMAMDACAAEAAQAwAwAANgAAACAgAgABAAEAMAEAAGYDAAAQEAIAAQABALAAAACWBAAAKAAAADAAAABgAAAAAQABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'));
        $file = new OPAL_File('sitemap.xml',$root_path);
        $file->saveData((new OPAL_Sitemap(true))->get());
    }
	
}