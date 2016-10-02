<?php

class OPMA_System_Cache extends OPAL_Controller {
	
	public function indexAction(){
		
		return '';
	}
	
	public function summaryHook(){
		$data = array();
		$methodCacheDir = new OPAL_File('methods','sites/'.OPAL_Portal::$sitecode.'/tmp/cache');
		list($data['methodCacheCount'],$data['methodCacheSize']) = $methodCacheDir->getDirCountAndSize();
		$staticCacheDir = new OPAL_File('static','sites/'.OPAL_Portal::$sitecode.'/tmp/cache');
		list($data['staticCacheCount'],$data['staticCacheSize']) = $staticCacheDir->getDirCountAndSize();
		return $this->templater->fetch('system/admin-cache-summary.phtml',$data);
	}
	
	public function clearMethodCacheAction(){
		if ($this->clearMethodCache()){
			return $this->msg(OPAL_Lang::t('ADMIN_CACHE_DELETED'), self::STATUS_COMPLETE, OP_WWW.'/admin/center/');
		} else {
			return $this->msg(OPAL_Lang::t('ADMIN_CACHE_NOT_DELETED'), self::STATUS_WARNING, OP_WWW.'/admin/center/');
		}
	}
	
	public function clearMethodCacheCli(){
		if ($this->clearMethodCache()){
			return '';
		} else {
			return 'OPMA_System_Cache::clearMethodCacheCli - Cache was not removed';
		}
	}
	
	private function clearMethodCache(){
		$dir = new OPAL_File('methods','sites/'.OPAL_Portal::$sitecode.'/tmp/cache');
		return $dir->delete();
	}
	
	public function clearStaticCacheAction(){
		if ($this->clearStaticCache()){
			return $this->msg(OPAL_Lang::t('ADMIN_CACHE_DELETED'), self::STATUS_COMPLETE, OP_WWW.'/admin/center/');
		} else {
			return $this->msg(OPAL_Lang::t('ADMIN_CACHE_NOT_DELETED'), self::STATUS_WARNING, OP_WWW.'/admin/center/');
		}
	}
	
	public function clearStaticCacheCli(){
		if ($this->clearStaticCache()){
			return '';
		} else {
			return 'OPMA_System_Cache::clearMethodCacheCli - Cache was not removed';
		}
	}
	
	private function clearStaticCache(){
		$dir = new OPAL_File('static','sites/'.OPAL_Portal::$sitecode.'/tmp/cache');
		return $dir->delete();
	}
	
}