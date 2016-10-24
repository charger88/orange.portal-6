<?php

class OPMO_System extends OPAL_Module {
	
	protected $privileges = array(
		'OPMC_System_Search::resultsActionDirect' => 'METHOD_SYSTEM_SEARCH_RESULTS',
		'OPMC_System_Search::resultsAjaxDirect'   => 'METHOD_SYSTEM_SEARCH_RESULTS',
	);
	
	protected function doInit(){
		OPAL_Theme::addScriptFile('modules/system/static/js/main.js');
		return true;
	}

	public function getInstallForm(){
		return new OPMX_System_Install();
	}

    protected function doInstall($params = []){
        $res = (new OPMI_System('system'))->installModule($params);
        $feedback = (new OPMI_Feedback('feedback'));
        $feedback->installModule($params);
        $feedback->createdAdditionalContent();
        $news = (new OPMI_News('news'));
        $news->installModule($params);
        $news->createdAdditionalContent();
		return $res;
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
		$adminMenu = parent::getAdminMenu();
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