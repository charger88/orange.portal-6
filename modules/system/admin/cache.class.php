<?php

class OPMA_System_Cache extends OPAL_Controller
{

	public function indexAction()
	{
		return '';
	}

	public function summaryBlockDirect()
	{
		$data = array();
		$systemCacheDir = new \Orange\FS\Dir('sites/' . OPAL_Portal::$sitecode . '/tmp/cache/system');
		try {
			list($data['systemCacheCount'], $data['systemCacheSize']) = $systemCacheDir->getDirInfo();
		} catch (\Orange\FS\FSException $e) {
			$data['systemCacheCount'] = $data['systemCacheSize'] = 0;
		}
		$staticCacheDir = new \Orange\FS\Dir('sites/' . OPAL_Portal::$sitecode . '/tmp/cache/static');
		try {
			list($data['staticCacheCount'], $data['staticCacheSize']) = $staticCacheDir->getDirInfo();
		} catch (\Orange\FS\FSException $e) {
			$data['staticCacheCount'] = $data['staticCacheSize'] = 0;
		}
		return $this->templater->fetch('system/admin-cache-summary.phtml', $data);
	}

	public function clearSystemCacheAction()
	{
		if ($this->clearSystemCache()) {
			return $this->msg(OPAL_Lang::t('ADMIN_CACHE_DELETED'), self::STATUS_COMPLETE, OP_WWW . '/admin/center/');
		} else {
			return $this->msg(OPAL_Lang::t('ADMIN_CACHE_NOT_DELETED'), self::STATUS_WARNING, OP_WWW . '/admin/center/');
		}
	}

	public function clearSystemCacheCli()
	{
		if ($this->clearSystemCache()) {
			return '';
		} else {
			return 'OPMA_System_Cache::clearSystemCacheCli - Cache was not removed';
		}
	}

	private function clearSystemCache()
	{
		try {
			OPAL_Portal::getInstance()->cache->reset();
			return true;
		} catch (\Orange\FS\FSException $e) {
			return false;
		}
	}

	public function clearStaticCacheAction()
	{
		if ($this->clearStaticCache()) {
			return $this->msg(OPAL_Lang::t('ADMIN_CACHE_DELETED'), self::STATUS_COMPLETE, OP_WWW . '/admin/center/');
		} else {
			return $this->msg(OPAL_Lang::t('ADMIN_CACHE_NOT_DELETED'), self::STATUS_WARNING, OP_WWW . '/admin/center/');
		}
	}

	public function clearStaticCacheCli()
	{
		if ($this->clearStaticCache()) {
			return '';
		} else {
			return 'OPMA_System_Cache::clearStaticCacheCli - Cache was not removed';
		}
	}

	private function clearStaticCache()
	{
		try {
			$dir = new \Orange\FS\Dir('sites/' . OPAL_Portal::$sitecode . '/tmp/cache/static');
			$dir->clear();
			return true;
		} catch (\Orange\FS\FSException $e) {
			return false;
		}
	}

}