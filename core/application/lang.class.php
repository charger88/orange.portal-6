<?php

class OPAL_Lang {
	
	private static $lang = [];
	
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
	
	public static function t($text,$params = []){
		$text = isset(self::$lang[$text]) ? self::$lang[$text] : $text;
		return $params ? vsprintf($text, $params) : $text;
	}
	
	public static function langs($langs = null){
		$return = [];
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
		$langs = [];
		$dirname = 'modules/system/lang';
		$files = new \Orange\FS\Dir($dirname);
		if ($dirFiles = $files->readDir()){
			foreach ($dirFiles as $file){
                if ($file instanceof \Orange\FS\File) {
                    if ($file->getExt() == 'json') {
                        if ($data = json_decode($file->getData(), true)) {
                            $langs[$data['code']] = $data;
                        }
                    }
                }
			}
		}
		return $langs;
	}
	
}