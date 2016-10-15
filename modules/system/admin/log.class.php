<?php

class OPMA_System_Log extends OPAL_Controller {
	
	public function indexAction(){
		$params = array();
		$params['offset']  = intval($this->getGet('offset',0));
        $params['limit']   = intval($this->getGet('limit',50));
        $params['log']     = $this->getGet('log_log',null);
		if ($params['list'] = OPAM_Log::loadLog($params)){
			foreach ($params['list'] as $item){
				$item->set('log_message',vsprintf(OPAL_Lang::t($item->get('log_message')),$item->get('log_vars')));
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
                    'text' => OPAL_Lang::t('ADMIN_DETAILS'),
                    'hint' => OPAL_Lang::t('ADMIN_DETAILS'),
                    'class' => 'icon icon-details',
                    'link' => '/' . $this->content->getSlug() . '/view/%id%',
                ),
            );
            return $this->templater->fetch('system/admin-list.phtml', $params);
        } else {
            return $this->msg(OPAL_Lang::t('ADMIN_NOTHING_FOUND'),self::STATUS_NOTFOUND);
        }
	}

    public function viewAction($id){
        $id = intval($id);
        $log = new OPAM_Log($id);
        return $this->templater->fetch('system/admin-log.phtml',array(
            'log'  => $log,
            'user' => new OPAM_User($log->get('log_user_id')),
            'refs' => $this->getRefs(),
        ));
    }
	
	public function lastBlockDirect(){
		return $this->templater->fetch('system/admin-log-last.phtml',array(
			'log' => OPAM_Log::loadLog(array(
				'date_start' => $this->getCookie('admin_log_dismiss_date'),
				'limit'      => $this->arg('limit', 8),
				'max_status' => $this->arg('maxstatus', self::STATUS_WARNING),
			)),
		));
	}
	
	private function getRefs(){
		$refs = array();
        $refs['log_log'] = array(
            'LOG_OPTIONS' => OPAL_Lang::t('LOG_OPTIONS'),
            'LOG_CONTENT' => OPAL_Lang::t('LOG_CONTENT'),
            'LOG_SYSTEM'  => OPAL_Lang::t('LOG_SYSTEM'),
            'LOG_FILES'   => OPAL_Lang::t('LOG_FILES'),
        );
        $refs['log_status'] = array(
            -1 => OPAL_Lang::t('STATUS_ALERT'),
            0 => OPAL_Lang::t('STATUS_ERROR'),
            1 => OPAL_Lang::t('STATUS_WARNING'),
            2 => OPAL_Lang::t('STATUS_NOTFOUND'),
            3 => OPAL_Lang::t('STATUS_INFO'),
            4 => OPAL_Lang::t('STATUS_OK'),
            5 => OPAL_Lang::t('STATUS_COMPLETE'),
        );
		return $refs;
	}
	
}