<?php

/**
 * Class OPDB_MCache
 */
class OPDB_MCache {

    /**
     * @var Memcache|null
     */
    public static $mc = null;

    /**
     * Connect to Memcache
     */
    public static function connectToMC(){
		if (OPDB_Config::$MCHost){
			if (class_exists('Memcache')){
				self::$mc = new Memcache();
				if (! self::$mc->connect( OPDB_Config::$MCHost, OPDB_Config::$MCPort ? OPDB_Config::$MCPort : 11211 ) ){
					self::$mc = false;
				}
			}
		}
	}

    /**
     * @param string $key
     * @param string $value
     * @param int $expire
     * @return bool
     */
    public static function set($key,$value,$expire = 60){
		if (self::$mc){
			if (OPDB_Config::$DBDebugMode) {
				OPDB_Functions::log('SET: '.$key,'','');
			}
			return self::$mc->set(OPDB_Config::$MCPrefix.$key, $value, 0, $expire);
		} else {
			return false;
		}
	}

    /**
     * @param string $key
     * @return string|bool
     */
    public static function get($key){
		if (self::$mc){
			if (OPDB_Config::$DBDebugMode) {
				OPDB_Functions::log('GET: '.$key,'','');
			}
			return self::$mc->get(OPDB_Config::$MCPrefix.$key);
		} else {
			return false;
		}
	}

    /**
     * @param $key
     * @return bool
     */
    public static function delete($key){
		if (self::$mc){
			if (OPDB_Config::$DBDebugMode) {
				OPDB_Functions::log('DELETE: '.$key,'','');
			}
			return self::$mc->delete(OPDB_Config::$MCPrefix.$key);
		} else {
			return false;
		}
	}

    /**
     * @return bool
     */
    public static function flushCache(){
		if (self::$mc){
			if (OPDB_Config::$DBDebugMode) {
				OPDB_Functions::log('FLUSH','','');
			}
			return self::$mc->flush();
		} else {
			return false;
		}
	}

    /**
     * @return bool
     */
    public static function getExtendedStats(){
		if (self::$mc){
			$stats = self::$mc->getExtendedStats();
			$result = false;
			foreach ($stats as $server){
				$result = $server;
			}
			return $result;
		} else {
			return false;
		}
	}
	
}