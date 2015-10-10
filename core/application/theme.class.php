<?php

/**
 * Class OPAL_Theme
 */
abstract class OPAL_Theme {

    /**
     * @var string
     */
    public $theme_url;
    /**
     * @var string
     */
    public $theme_name = '';

    /**
     * @var array
     */
    public $folders = array();

    /**
     * @var array
     */
    protected static $head_style = array();
    /**
     * @var array
     */
    protected static $head_scripts = array();

    /**
     * Constructor
     */
    public function __construct(){
		$this->theme_url = OP_WWW.'/themes/'.$this->theme_name.'/';
		self::addScriptFile('modules/system/static/js/jquery.min.js');
	}

    /**
     * @param string $class
     * @return string
     */
    protected function getThemeNameByClass($class){
		$theme_name = explode('_', $class);
		array_shift($theme_name);
		return strtolower(implode('_', $theme_name));
	}

    /**
     * @return array
     */
    abstract public function getThemeAreas();

    /**
     * @return array
     */
    public function getAdminAreas(){
		return array(
			'admin-column' => 'ADMIN_AREA_COLUMN',
			'admin-docend' => 'ADMIN_AREA_DOCEND',
			'admin-index'  => 'ADMIN_AREA_INDEX',
		);
	}

    /**
     * @param string $filename
     * @param bool $external
     */
    public static function addStyleFile($filename,$external = false){
		self::$head_style[] = $external ? $filename : OP_WWW.'/'.$filename;
	}

    /**
     * @param string $filename
     * @param bool $external
     */
    public static function addScriptFile($filename,$external = false){
		self::$head_scripts[] = $external ? $filename : OP_WWW.'/'.$filename;
	}

    /**
     * @return array
     */
    public function getHeadStyleFiles(){
		if (self::$head_style && OPAL_Portal::config('system_cache_css',false)){
			$filename = 'tmp/cache/static/style_'.md5(implode(';', self::$head_style)).'.css';
			$file = new OPAL_File($filename);
			$data = '';
			if (!$file->file){
				foreach (self::$head_style as $css_filename){
					if ($css = OPAL_Downloader::download($css_filename)){
						//TODO Add support for @import directives
						$urls = array();
						preg_match_all('/background[-image]?:.*[\s]*url\(["|\']+(.*)["|\']+\)/', $css, $urls, PREG_SET_ORDER);
						foreach ($urls as $url){
							$url = $url[1];
							$css = str_replace($url, dirname($css_filename).'/'.$url, $css);
						}
						$data .= "/* File: $css_filename */\n\n".$css."\n\n";
					}
				}
				$file->saveData(trim($data));
			}
			self::$head_style = array(OP_WWW.'/'.$filename);
		}
		return self::$head_style;
	}

    /**
     * @return array
     */
    public function getHeadScriptFiles(){
		if (self::$head_scripts && OPAL_Portal::config('system_cache_js',false)){
			$filename = 'tmp/cache/static/script_'.md5(implode(';', self::$head_scripts)).'.js';
			$file = new OPAL_File($filename);
			$data = '';
			if (!$file->file){
				foreach (self::$head_scripts as $js_filename){
					if ($js = OPAL_Downloader::download($js_filename)){
						$data .= "/* File: $js_filename */\n\n".$js."\n\n";
					}
				}				
				$file->saveData(trim($data));
			}
			self::$head_scripts = array(OP_WWW.'/'.$filename);
		}
		return self::$head_scripts;
	}

    /**
     * @param string $prefix
     * @return array
     */
    public function getTemplatesList($prefix = 'main-'){
		$templates = array();
		if ($prefix){
			foreach ($this->folders as $theme){
				$dir = new OPAL_File('themes/'.$theme.'/templates');
				if ($dir->dir){
					$files = $dir->dirFiles();
					foreach ($files as $file){
						if (strpos($file,$prefix) === 0){
							$templates[$file] = $file . ' / ' . $theme;
						}
					}
				}
			}
			ksort($templates);
		}
		return $templates;
	}

    /**
     * @param OPAM_Content $content
     * @return string
     */
    public function getContentTemplate($content){
		$type = new OPAM_Content_Type('content_type_code',$content->get('content_type'));
		$module = explode('_',$type->getClass());
		$module = ($module[0] == 'OPMM') ? strtolower($module[1]) : 'system';
		$template = null;
		foreach ($this->folders as $theme){
			if (is_file(OP_SYS_ROOT.'themes/'.$theme.'/templates/modules/'.$module.'/content-'.$type->get('content_type_code').'.phtml')){
				$template = 'content-'.$type->get('content_type_code').'.phtml';
				break;
			}
		}		
		if (is_null($template)){
            $module = 'system';
			if ($type->get('content_type_type') == 2){
				$template = 'content-page.phtml';
			} else if ($type->get('content_type_type') == 2){
				$template = 'content-block.phtml';
			} else if ($type->get('content_type_code') == 'admin'){
				$template = 'content-admin.phtml';
			} else {
				$template = 'content-default.phtml';
			}
		}
		return $module . '/' . $template;
	}

    /**
     * @return array
     */
    public function getThemeInfo(){
		$json = new OPAL_File('info.json','themes/'.$this->theme_name);
		$info = array();
		if ($json = $json->getData()){
			if ($json = json_decode($json,true)){
				$info = $json;
			}
		}
		return $info;
	}

    /**
     * @param string|null $field
     * @return array
     */
    public static function getAvalibleThemes($field = null){
		$dirname = 'themes';
		$files = new OPAL_File($dirname);
		$themes = array();
		if ($dirFiles = $files->dirFiles()){
			foreach ($dirFiles as $filename){
				$file = new OPAL_File('info.json',$dirname.'/'.$filename);
				if ($file->getExt() == 'json'){
					if ($data = json_decode($file->getData(),true)){
						$themes[$data['code']] = is_null($field) ? $data : $data[$field];
					}
				}
			}
		}
		return $themes;
	}
	
}