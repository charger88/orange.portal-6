<?php

class OPMC_System_Search extends OPAL_Controller {
	
	public function indexAction(){
		$return = $this->form();
		if ($search = $this->getGet('search')){
			$return .= $this->results($search);
		}
        return $return;
	}

	public function resultsActionDirect(){
		return $this->results($this->getGet('search'));
	}

	public function resultsAjaxDirect(){
		return $this->results($this->getGet('search'));
	}
	
	private function results($search){
        $search = trim($search);
        OPAL_Portal::getInstance()->content->set('content_title', OPAL_Lang::t('SEARCH'));
        if (empty($search)){
            return $this->msg(OPAL_Lang::t('SEARCH_REQUEST_IS_EMPTY'), self::STATUS_NOTFOUND);
        } else if (strlen($search) > 256){
            return $this->msg(OPAL_Lang::t('SEARCH_REQUEST_TOO_LARGE'), self::STATUS_NOTFOUND);
        } else if (substr_count($search,' ') >= 10){
            return $this->msg(OPAL_Lang::t('SEARCH_REQUEST_TOO_MUCH_WORDS'), self::STATUS_NOTFOUND);
        } else {
            $searchable_types = OPAL_Portal::getInstance()->processHooks('get_searchable_types');
            $baselimit = $this->arg('limit', 50);
            $results = OPAM_Page::getList(array(
                'types' => $searchable_types,
                'search' => $search,
                'searchmode' => 1,
                'access_user' => $this->user,
                'lang' => [OPAL_Portal::$sitelang, ''],
            ), 'OPAM_Page');
            if (($limit = ($baselimit - count($results))) > 0) {
                $results += OPAM_Page::getList(array(
                    'types' => $searchable_types,
                    'search' => $search,
                    'searchmode' => 2,
                    'access_user' => $this->user,
                    'lang' => [OPAL_Portal::$sitelang, ''],
                    'exclude' => array_keys($results),
                    'limit' => $limit,
                ), 'OPAM_Page');
            }
            if (($limit = ($baselimit - count($results))) > 0) {
                $results += OPAM_Page::getList(array(
                    'types' => $searchable_types,
                    'search' => str_replace(' ', '%', $search),
                    'searchmode' => 1,
                    'access_user' => $this->user,
                    'lang' => [OPAL_Portal::$sitelang, ''],
                    'exclude' => array_keys($results),
                    'limit' => $limit,
                ), 'OPAM_Page');
            }
            if (($limit = ($baselimit - count($results))) > 0) {
                $results += OPAM_Page::getList(array(
                    'types' => $searchable_types,
                    'search' => str_replace(' ', '%', $search),
                    'searchmode' => 2,
                    'access_user' => $this->user,
                    'lang' => [OPAL_Portal::$sitelang, ''],
                    'exclude' => array_keys($results),
                    'limit' => $limit,
                ), 'OPAM_Page');
            }
            //TODO Add some less relevant requests
            $this->content->set('content_type', 'SEARCH_RESULTS');
            $this->content->setField('seo_hidden', true);
            if ($results) {
                return $this->templater->fetch('system/search-result.phtml', [
                    'search' => $search,
                    'results' => $results
                ]);
            } else {
                return $this->msg(OPAL_Lang::t('NOTHING_FOUND'), self::STATUS_NOTFOUND);
            }
        }
	}
	
	public function formAction(){
		return $this->form();
	}
	
	public function formAjax(){
		return $this->form();
	}
	
	public function formBlock(){
		return $this->form();
	}
	
	private function form(){
        if (OPAM_Privilege::hasPrivilege('METHOD_SYSTEM_SEARCH_RESULTS',$this->user)) {
            $form = new OPMF_System_Search();
            $form->setAction(OP_WWW . '/module/system/search/results');
            $form->setMethod(\Orange\Forms\Form::METHOD_GET);
            $form->setValues(array('search' => $this->getGet('search')));
            return $form->getHTML();
        } else {
            return '';
        }
	}
	
}