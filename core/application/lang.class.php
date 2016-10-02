<?php

class OPAL_Lang {
	
	private static $lang = array();
	
	public static function load($folder,$lang_to_load,$baselang = 'en'){
		if ($lang_to_load != $baselang){
			$filename = OP_SYS_ROOT.trim($folder,'/').'/'.$baselang.'.php';
			if (is_file($filename)){
                $lang = include $filename;
				self::$lang = array_merge($lang,self::$lang);
			}
		}
		$filename = OP_SYS_ROOT.trim($folder,'/').'/'.$lang_to_load.'.php';
        if (is_file($filename)){
            $lang = include $filename;
			self::$lang = array_merge(self::$lang,$lang);
		}
	}
	
	public static function t($text,$params = array()){
		$text = isset(self::$lang[$text]) ? self::$lang[$text] : $text;
		return $params ? vsprintf($text, $params) : $text;
	}
	
	public static function langs($langs = null){
		$return = array();
		$avalible_langs = self::getAvalibleLangsInfo();
		if (is_null($langs)){
			if ($avalible_langs){
				foreach ($avalible_langs as $l => $lData){
					$return[$l] = isset($avalible_langs[$l]) ? $lData['name'] : $l;
				}
			}
		} else {
			if ($langs){
				foreach ($langs as $l){
					$return[$l] = isset($avalible_langs[$l]) ? $avalible_langs[$l]['name'] : $l;
				}
			}
		}
		return $return;
	}
	
	public static function getAvalibleLangsInfo(){
		$langs = array();
		$dirname = 'modules/system/lang';
		$files = new OPAL_File($dirname);
		if ($dirFiles = $files->dirFiles()){
			foreach ($dirFiles as $filename){
				$file = new OPAL_File($filename,$dirname);
				if ($file->getExt() == 'json'){
					if ($data = json_decode($file->getData(),true)){
						$langs[$data['code']] = $data;
					}
				}
			}
		}
		return $langs;
	}
	
}