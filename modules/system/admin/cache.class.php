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
		$methodCacheDir = new \Orange\FS\Dir('sites/' . OPAL_Portal::$sitecode . '/tmp/cache/methods');
		try {
			list($data['methodCacheCount'], $data['methodCacheSize']) = $methodCacheDir->getDirInfo();
		} catch (\Orange\FS\FSException $e) {
			$data['methodCacheCount'] = $data['methodCacheSize'] = 0;
		}
		$staticCacheDir = new \Orange\FS\Dir('sites/' . OPAL_Portal::$sitecode . '/tmp/cache/static');
		try {
			list($data['staticCacheCount'], $data['staticCacheSize']) = $staticCacheDir->getDirInfo();
		} catch (\Orange\FS\FSException $e) {
			$data['staticCacheCount'] = $data['staticCacheSize'] = 0;
		}
		return $this->templater->fetch('system/admin-cache-summary.phtml', $data);
	}

	public function clearMethodCacheAction()
	{
		if ($this->clearMethodCache()) {
			return $this->msg(OPAL_Lang::t('ADMIN_CACHE_DELETED'), self::STATUS_COMPLETE, OP_WWW . '/admin/center/');
		} else {
			return $this->msg(OPAL_Lang::t('ADMIN_CACHE_NOT_DELETED'), self::STATUS_WARNING, OP_WWW . '/admin/center/');
		}
	}

	public function clearMethodCacheCli()
	{
		if ($this->clearMethodCache()) {
			return '';
		} else {
			return 'OPMA_System_Cache::clearMethodCacheCli - Cache was not removed';
		}
	}

	private function clearMethodCache()
	{
		try {
			$dir = new \Orange\FS\Dir('sites/' . OPAL_Portal::$sitecode . '/tmp/cache/methods');
			$dir->clear();
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
			return 'OPMA_System_Cache::clearMethodCacheCli - Cache was not removed';
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