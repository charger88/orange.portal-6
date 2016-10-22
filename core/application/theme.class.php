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
    public $folders = [];

    /**
     * @var array
     */
    protected static $head_style = [];
    /**
     * @var array
     */
    protected static $head_scripts = [];
    /**
     * @var array
     */
    protected static $rss_channels = [];

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
		return [
			'admin-column' => 'ADMIN_AREA_COLUMN',
			'admin-docend' => 'ADMIN_AREA_DOCEND',
			'admin-index'  => 'ADMIN_AREA_INDEX',
		];
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
     * @param string $filename
     * @param bool $external
     */
    public static function addRSSChannel($url,$title,$external = false){
        self::$rss_channels[$external ? $url : OP_WWW.'/'.$url] = $title;
    }

    /**
     * @return array
     */
    public function getHeadStyleFiles(){
		if (self::$head_style && OPAL_Portal::config('system_cache_css',false)){
			$filename = 'sites/'.OPAL_Portal::$sitecode.'/tmp/cache/static/style_'.md5(implode(';', self::$head_style)).'.css';
			$file = new \Orange\FS\File($filename);
			$data = '';
			if (!$file->exists()){
				foreach (self::$head_style as $css_filename){
					if ($css = OPAL_Downloader::download($css_filename)){
						//TODO Add support for @import directives
						$urls = [];
						preg_match_all('/background(\-image)?:.*[\s]*url\(["|\']+(.*)["|\']+\)/', $css, $urls, PREG_SET_ORDER);
						foreach ($urls as $url){
							$url = $url[2];
							$css = str_replace($url, dirname($css_filename).'/'.$url, $css);
						}
						$data .= "/* File: $css_filename */\n\n".$css."\n\n";
					}
				}
				$file->save(trim($data));
			}
			self::$head_style = [OP_WWW.'/'.$filename];
		}
		return self::$head_style;
	}

    /**
     * @return array
     */
    public function getHeadScriptFiles(){
        if (self::$head_scripts && OPAL_Portal::config('system_cache_js',false)){
            $filename = 'sites/'.OPAL_Portal::$sitecode.'/tmp/cache/static/script_'.md5(implode(';', self::$head_scripts)).'.js';
            $file = new \Orange\FS\File($filename);
            $data = '';
            if (!$file->exists()){
                foreach (self::$head_scripts as $js_filename){
                    if ($js = OPAL_Downloader::download($js_filename)){
                        $data .= "/* File: $js_filename */\n\n".$js."\n\n";
                    }
                }
                $file->save(trim($data));
            }
            self::$head_scripts = [OP_WWW.'/'.$filename];
        }
        return self::$head_scripts;
    }

    /**
     * @return array
     */
    public function getRSSChannels(){
        return self::$rss_channels;
    }

    /**
     * @param string $prefix
     * @return array
     */
    public function getTemplatesList($prefix = 'main-'){
		$templates = [];
		if ($prefix){
			foreach ($this->folders as $theme){
				$dir = new \Orange\FS\Dir('themes/'.$theme.'/templates');
				if ($dir->exists()){
					$files = $dir->readDir();
					foreach ($files as $file){
						if (strpos($file->getName(),$prefix) === 0){
							$templates[$file->getName()] = $file->getName() . ' / ' . $theme;
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
            if (is_file(OP_SYS_ROOT.'modules/'.$module.'/templates/content-'.$type->get('content_type_code').'.phtml')){
                $template = 'content-'.$type->get('content_type_code').'.phtml';
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
		$json = new \Orange\FS\File('themes/'.$this->theme_name, 'info.json');
		$info = [];
		if ($json = $json->getData()){
			if ($json = json_decode($json,true)){
				$info = $json;
			}
		}
		return $info;
	}

    /**
     * @param string $lang
     * @return OPAL_Theme
     */
    public function loadLanguages($lang){
        foreach ($this->folders as $folder) {
            OPAL_Lang::load('themes/'.$folder.'/lang', $lang);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getEditorCSSFile(){
        $css_file = '';
        foreach ($this->folders as $folder) {
            $css = new \Orange\FS\File('themes/'.$folder.'/static', 'editor.css');
            if ($css->exists()){
                $css_file = 'themes/'.$folder.'/static/editor.css';
                break;
            }
        }
        return $css_file;
    }

    /**
     * @param string|null $field
     * @return array
     */
    public static function getAvailableThemes($field = null){
		$files = new \Orange\FS\Dir('themes');
		$themes = [];
        foreach ($files->readDir() as $dir){
            $file = new \Orange\FS\File($dir, 'info.json');
            if ($file->getExt() == 'json'){
                if ($data = json_decode($file->getData(),true)){
                    $themes[$data['code']] = is_null($field) ? $data : $data[$field];
                }
            }
        }
		return $themes;
	}

}