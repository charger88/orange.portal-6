<?php

class OPMO_System extends OPAL_Module {
	
	protected $privileges = array(
		'OPMC_System_Search::resultsActionDirect' => 'METHOD_SYSTEM_SEARCH_RESULTS',
		'OPMC_System_Search::resultsAjaxDirect'   => 'METHOD_SYSTEM_SEARCH_RESULTS',
	);
	
	protected function doInit(){
		$this->initHooks();
		OPAL_Theme::addScriptFile('modules/system/static/js/main.js');
		return true;
	}
	
	private function initHooks(){
		OPAL_Portal::addHook('initUser_noUID', 'OPMC_System_Signin', 'go');
		OPAL_Portal::addHook('adminCenter_index', 'OPMA_System_Center', 'system');
		OPAL_Portal::addHook('adminCenter_index', 'OPMA_System_Cache', 'summary');
        OPAL_Portal::addHook('adminCenter_index', 'OPMA_System_Log', 'last');
        OPAL_Portal::addHook('adminCenter_index', 'OPMA_System_Sitemap', 'sitemap');
        OPAL_Portal::addHook('adminCenter_sitemap', 'OPMA_System_Sitemap', 'buildSitemap');
        OPAL_Portal::addHook('adminAccess_privileges', __CLASS__, 'getPrivilegesList');
	}
	
	public function getInstallForm(){
		return new OPMX_System_Install();
	}

    protected function doInstall($params = array()){
		$i = new OPMI_System('system');
		return $i->install($params);
	}

    protected function doEnable(){
		return null;
	}

    protected function doDisable(){
		return null;
	}

    protected function doUninstall(){
		return null;
	}

	public function getAdminMenu(){
		$adminMenu = array(
			'pages' => array(
				'name' => 'ADMIN_PAGES',
				'url' => '/admin/pages',
				'icon' => '/modules/system/static/icons/pages.png',
				'order' => 0,
				'sub' => array(
					'tree' => array(
						'name' => 'ADMIN_PAGES_TREE',
						'url' => '/admin/pages',
						'icon' => '/modules/system/static/icons/pages-tree.png',
						'order' => 10
					),
				)
			),
			'blocks' => array(
				'name' => 'ADMIN_BLOCKS',
				'url' => '/admin/blocks',
				'icon' => '/modules/system/static/icons/blocks.png',
				'order' => 10,
				'sub' => array(
					'list' => array(
						'name' => 'ADMIN_BLOCKS_LIST',
						'url' => '/admin/blocks',
						'icon' => '/modules/system/static/icons/blocks.png',
						'order' => 10
					),
				)
			),
			'types' => array(
				'name' => 'ADMIN_CONTENT',
				'url' => '/admin/types',
				'icon' => '/modules/system/static/icons/types.png',
				'order' => 20,
				'sub' => array(
					'types' => array(
						'name' => 'ADMIN_TYPES',
						'url' => '/admin/types',
						'icon' => '/modules/system/static/icons/types.png',
						'order' => 10
					),
				),
			),
            'files' => array(
                'name' => 'ADMIN_FILES',
                'url' => '/admin/files',
                'icon' => '/modules/system/static/icons/files.png',
                'order' => 30,
                'sub' => array(
                    'files-list' => array(
                        'name' => 'ADMIN_FILES',
                        'url' => '/admin/files',
                        'icon' => '/modules/system/static/icons/files.png',
                        'order' => 10
                    ),
                    'media' => array(
                        'name' => 'ADMIN_FILES_MEDIA',
                        'url' => '/admin/files/media',
                        'icon' => '/modules/system/static/icons/media.png',
                        'order' => 20
                    ),
                ),
            ),
            'users' => array(
                'name' => 'ADMIN_USERS',
                'url' => '/admin/users',
                'icon' => '/modules/system/static/icons/users.png',
                'order' => 40,
                'sub' => array(
                    'admin-users-index' => array(
                        'name' => 'ADMIN_USERS',
                        'url' => '/admin/users',
                        'icon' => '/modules/system/static/icons/users.png',
                        'order' => 10
                    ),
                    'admin-users-list' => array(
                        'name' => 'ADMIN_USERS_LIST',
                        'url' => '/admin/users/list',
                        'icon' => '/modules/system/static/icons/users-list.png',
                        'order' => 20
                    ),
                    'admin-users-new' => array(
                        'name' => 'ADMIN_CREATE_NEW_USER',
                        'url' => '/admin/users/new',
                        'icon' => '/modules/system/static/icons/user-new.png',
                        'order' => 30
                    ),
                )
            ),
            'modules' => array(
                'name' => 'ADMIN_MODULES',
                'url' => '/admin/modules',
                'icon' => '/modules/system/static/icons/modules.png',
                'order' => 50,
                'sub' => array(
                    'modules-installed' => array(
                        'name' => 'ADMIN_MODULES_INSTALLED',
                        'url' => '/admin/modules',
                        'icon' => '/modules/system/static/icons/modules.png',
                        'order' => 10
                    ),
                    'modules-install' => array(
                        'name' => 'ADMIN_MODULES_INSTALL',
                        'url' => '/admin/modules/new',
                        'icon' => '/modules/system/static/icons/add.png',
                        'order' => 20
                    ),
                    'modules-discover' => array(
                        'name' => 'ADMIN_MODULES_DISCOVER',
                        'url' => '/admin/modules/discover',
                        'icon' => '/modules/system/static/icons/world.png',
                        'order' => 30
                    ),
                )
            ),
            'options' => array(
                'name' => 'ADMIN_OPTIONS',
                'url' => '/admin/options',
                'icon' => '/modules/system/static/icons/options.png',
                'order' => 60,
                'sub' => array(
                    'system' => array(
                        'name' => 'ADMIN_OPTIONS_SYSTEM',
                        'url' => '/admin/options/system',
                        'icon' => '/modules/system/static/icons/options-system.png',
                        'order' => 10
                    ),
                    'move' => array(
                        'name' => 'ADMIN_OPTIONS_MOVE',
                        'url' => '/admin/options/move',
                        'icon' => '/modules/system/static/icons/options-domain.png',
                        'order' => 20
                    ),
                )
            ),
            'access' => array(
                'name' => 'ADMIN_ACCESS',
                'url' => '/admin/panel',
                'icon' => '/modules/system/static/icons/access.png',
                'order' => 70,
                'sub' => array(
                    'admin-groups' => array(
                        'name' => 'ADMIN_GROUPS',
                        'url' => '/admin/groups',
                        'icon' => '/modules/system/static/icons/groups.png',
                        'order' => 10
                    ),
                    'admin-privileges' => array(
                        'name' => 'ADMIN_PRIVILEGES',
                        'url' => '/admin/privileges',
                        'icon' => '/modules/system/static/icons/privileges.png',
                        'order' => 20
                    ),
                    'admin-panel' => array(
                        'name' => 'ADMIN_ADMIN_PAGES',
                        'url' => '/admin/panel',
                        'icon' => '/modules/system/static/icons/access-admin.png',
                        'order' => 30
                    ),
                ),
            ),
			'log' => array(
				'name' => 'ADMIN_LOGS',
				'url' => '/admin/log',
				'icon' => '/modules/system/static/icons/log.png',
				'order' => 80
			),
		);
		if ($customTypes = OPAM_Content_Type::getTypes(null,null,'*')){
			$i = 100;
			foreach ($customTypes as $cType){
				if ($cType->get('content_type_type') == 1){
					$menu = 'pages';
					$addlink = '/admin/pages/new'.($cType->get('content_type_code') == 'page' ? '' : '/'.$cType->get('content_type_code'));
				} else if ($cType->get('content_type_type') == 2){
					$menu = 'blocks';
					$addlink = '/admin/blocks/new'.($cType->get('content_type_code') == 'block' ? '' : '/'.$cType->get('content_type_code'));
				} else if ($cType->get('content_type_type') == 4){
					$menu = 'types';
					$addlink = '/admin/content/new/'.$cType->get('content_type_code');
				} else {
					$menu = $addlink = '';
				}
				if ($menu){
					$adminMenu[$menu]['sub']['add_'.$cType->get('content_type_code')] = array(
						'name' => OPAL_Lang::t('ADMIN_ADD_NEW').' '.OPAL_Lang::t($cType->get('content_type_name')),
						'url' => $addlink,
						'icon' => '/modules/system/static/icons/'.$menu.'-new.png',
						'order' => $i,
					);
					$i++;
				}
			}
		}
		return $adminMenu;
	}
	
}