<?php

class OPMC_System_Search extends \Orange\Portal\Core\App\Controller
{

	public function indexAction()
	{
		$return = $this->form();
		if ($search = $this->getGet('search')) {
			$return .= $this->results($search);
		}
		return $return;
	}

	public function resultsActionDirect()
	{
		return $this->results($this->getGet('search'));
	}

	public function resultsAjaxDirect()
	{
		return $this->results($this->getGet('search'));
	}

	private function results($search)
	{
		$search = trim($search);
		\Orange\Portal\Core\App\Portal::getInstance()->content->set('content_title', \Orange\Portal\Core\App\Lang::t('SEARCH'));
		if (empty($search)) {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('SEARCH_REQUEST_IS_EMPTY'), self::STATUS_NOTFOUND);
		} else if (strlen($search) > 256) {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('SEARCH_REQUEST_TOO_LARGE'), self::STATUS_NOTFOUND);
		} else if (substr_count($search, ' ') >= 10) {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('SEARCH_REQUEST_TOO_MUCH_WORDS'), self::STATUS_NOTFOUND);
		} else {
			$searchable_types = \Orange\Portal\Core\App\Portal::getInstance()->processHooks('get_searchable_types');
			$baselimit = $this->arg('limit', 50);
			$results = \Orange\Portal\Core\Model\Page::getList([
				'types' => $searchable_types,
				'search' => $search,
				'searchmode' => 1,
				'access_user' => $this->user,
				'lang' => [\Orange\Portal\Core\App\Portal::$sitelang, ''],
			], '\Orange\Portal\Core\Model\Page');
			if (($limit = ($baselimit - count($results))) > 0) {
				$results += \Orange\Portal\Core\Model\Page::getList([
					'types' => $searchable_types,
					'search' => $search,
					'searchmode' => 2,
					'access_user' => $this->user,
					'lang' => [\Orange\Portal\Core\App\Portal::$sitelang, ''],
					'exclude' => array_keys($results),
					'limit' => $limit,
				], '\Orange\Portal\Core\Model\Page');
			}
			if (($limit = ($baselimit - count($results))) > 0) {
				$results += \Orange\Portal\Core\Model\Page::getList([
					'types' => $searchable_types,
					'search' => str_replace(' ', '%', $search),
					'searchmode' => 1,
					'access_user' => $this->user,
					'lang' => [\Orange\Portal\Core\App\Portal::$sitelang, ''],
					'exclude' => array_keys($results),
					'limit' => $limit,
				], '\Orange\Portal\Core\Model\Page');
			}
			if (($limit = ($baselimit - count($results))) > 0) {
				$results += \Orange\Portal\Core\Model\Page::getList([
					'types' => $searchable_types,
					'search' => str_replace(' ', '%', $search),
					'searchmode' => 2,
					'access_user' => $this->user,
					'lang' => [\Orange\Portal\Core\App\Portal::$sitelang, ''],
					'exclude' => array_keys($results),
					'limit' => $limit,
				], '\Orange\Portal\Core\Model\Page');
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
				return $this->msg(\Orange\Portal\Core\App\Lang::t('NOTHING_FOUND'), self::STATUS_NOTFOUND);
			}
		}
	}

	public function formAction()
	{
		return $this->form();
	}

	public function formAjax()
	{
		return $this->form();
	}

	public function formBlock()
	{
		return $this->form();
	}

	private function form()
	{
		if (\Orange\Portal\Core\Model\Privilege::hasPrivilege('METHOD_SYSTEM_SEARCH_RESULTS', $this->user)) {
			$form = new OPMF_System_Search();
			$form->setAction(OP_WWW . '/module/system/search/results');
			$form->setMethod(\Orange\Forms\Form::METHOD_GET);
			$form->setValues(['search' => $this->getGet('search')]);
			return $form->getHTML();
		} else {
			return '';
		}
	}

}