<?php

class OPMA_System_Log extends \Orange\Portal\Core\App\Controller
{

	public function indexAction()
	{
		$params = array();
		$params['offset'] = intval($this->getGet('offset', 0));
		$params['limit'] = intval($this->getGet('limit', 50));
		$params['log'] = $this->getGet('log_log', null);
		if ($params['list'] = \Orange\Portal\Core\Model\Log::loadLog($params)) {
			foreach ($params['list'] as $item) {
				$item->set('log_message', vsprintf(\Orange\Portal\Core\App\Lang::t($item->get('log_message')), $item->get('log_vars')));
			}
		}
		if ($params['list'] || $params['offset']) {
			$params['class_fields'] = array();
			$params['refs'] = $this->getRefs();
			$params['columns'] = array(
				'log_time' => array('width' => 25),
				'log_log' => array('width' => 15, 'filter' => true),
				'log_message' => array('width' => 60),
				'_view' => array(
					'title' => '',
					'text' => \Orange\Portal\Core\App\Lang::t('ADMIN_DETAILS'),
					'hint' => \Orange\Portal\Core\App\Lang::t('ADMIN_DETAILS'),
					'class' => 'icon icon-details',
					'link' => '/' . $this->content->getSlug() . '/view/%id%',
				),
			);
			return $this->templater->fetch('system/admin-list.phtml', $params);
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_NOTHING_FOUND'), self::STATUS_NOTFOUND);
		}
	}

	public function viewAction($id)
	{
		$id = intval($id);
		$log = new \Orange\Portal\Core\Model\Log($id);
		return $this->templater->fetch('system/admin-log.phtml', array(
			'log' => $log,
			'user' => new \Orange\Portal\Core\Model\User($log->get('log_user_id')),
			'refs' => $this->getRefs(),
		));
	}

	public function lastBlockDirect()
	{
		return $this->templater->fetch('system/admin-log-last.phtml', array(
			'log' => \Orange\Portal\Core\Model\Log::loadLog(array(
				'date_start' => $this->getCookie('admin_log_dismiss_date'),
				'limit' => $this->arg('limit', 8),
				'max_status' => $this->arg('maxstatus', self::STATUS_WARNING),
			)),
		));
	}

	private function getRefs()
	{
		$refs = array();
		$refs['log_log'] = array(
			'LOG_OPTIONS' => \Orange\Portal\Core\App\Lang::t('LOG_OPTIONS'),
			'LOG_CONTENT' => \Orange\Portal\Core\App\Lang::t('LOG_CONTENT'),
			'LOG_SYSTEM' => \Orange\Portal\Core\App\Lang::t('LOG_SYSTEM'),
			'LOG_FILES' => \Orange\Portal\Core\App\Lang::t('LOG_FILES'),
		);
		$refs['log_status'] = array(
			-1 => \Orange\Portal\Core\App\Lang::t('STATUS_ALERT'),
			0 => \Orange\Portal\Core\App\Lang::t('STATUS_ERROR'),
			1 => \Orange\Portal\Core\App\Lang::t('STATUS_WARNING'),
			2 => \Orange\Portal\Core\App\Lang::t('STATUS_NOTFOUND'),
			3 => \Orange\Portal\Core\App\Lang::t('STATUS_INFO'),
			4 => \Orange\Portal\Core\App\Lang::t('STATUS_OK'),
			5 => \Orange\Portal\Core\App\Lang::t('STATUS_COMPLETE'),
		);
		return $refs;
	}

}