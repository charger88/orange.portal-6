<?php

/**
 * Class OPAL_Controller
 */
class OPAL_Controller {

	const STATUS_ALERT    = -1;
	const STATUS_ERROR    =  0;
	const STATUS_WARNING  =  1;
	const STATUS_NOTFOUND =  2;
	const STATUS_INFO     =  3;
	const STATUS_OK       =  4;
	const STATUS_COMPLETE =  5;
	
	/**
	 * @var OPAM_Content
	 */
	protected $content;
	
	/**
	 * @var OPAM_User
	 */
	protected $user;

    /**
     * @var OPAL_SessionInterface
     */
    protected $session;
	
	/**
	 * @var OPAL_Templater
	 */
	protected $templater;
	
	/**
	 * @var array
	 */
	protected $args = array();
	
	/**
	 * @var array
	 */
	protected $cachemap = array();
	
	/**
	 * @var string|null
	 */
	private static $ip = null;
	
	/**
	 * @param OPAM_Content $content
     * @param OPAM_User $user
	 * @param OPAL_Templater $templater
	 * @param array $args
	 */
	public function __construct($content,$user,$session,$templater,$args = array()){
		$this->content = $content;
		$this->user = $user;
        $this->session = $session;
		$this->templater = $templater;
		$this->args = $args;
		foreach ($this->args as $key => $value){
			if ($value && ($value{0} == '@')){
				$this->args[$key] = $this->content->get(substr($value, 1));
			}
		}
	}
	
	/**
	 * @param string $param
	 * @param string | boolean | integer | float $default
	 * @return string | boolean | integer | float
	 */
	protected function arg($param,$default = null){
		return isset($this->args[$param]) ? $this->args[$param] : $default;
	}

    /**
     * @param $name
     * @param mixed $default
     * @return mixed
     */
    protected function getGet($name,$default = null){
		return isset($_GET[$name]) ? $_GET[$name] : $default;
	}

    /**
     * @param $name
     * @param mixed $default
     * @return mixed
     */
    protected function getPost($name,$default = null){
		return isset($_POST[$name]) ? $_POST[$name] : $default;
	}

    /**
     * @return array
     */
    protected function getPostArray(){
		return $_POST;
	}

    /**
     * @return string
     */
    protected function getPostRaw(){
		return file_get_contents('php://input');
	}

    /**
     * @param $name
     * @param string|null $default
     * @return string|null
     */
    protected function getServer($name,$default = null){
		return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
	}

    /**
     * @param string $name
     * @return array|null
     */
    protected function getFile($name){
        return isset($_FILES[$name]) ? $_FILES[$name] : null;
    }

    /**
     * @param $name
     * @param string|null $default
     * @return string|null
     */
    protected function getCookie($name,$default = null){
		return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
	}

    /**
     * @param string $name
     * @param string $value
     */
    protected function setCookie($name,$value){
		$_COOKIE[$name] = $value;
		setcookie($name,$value,strtotime('+60 days'));
	}

    /**
     * @return string
     */
    protected function getIP(){
		if (is_null(self::$ip)){
			self::$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
			if (isset($_SERVER['X-FORWARDED-FOR']) && in_array(self::$ip, OPAL_Portal::config('system_proxy_ip',array()))){
				self::$ip = $_SERVER['X-FORWARDED-FOR'];
			}
		}
		return self::$ip;
	}

    /**
     * @param string $template
     * @param string $html
     * @param array $data
     * @return string|null
     */
    protected function wrapContentWithTemplate($template,$html = '',$data = array()){
		$data['html'] = $html;
		return $this->templater->fetch($template,$data);
	}

    /**
     * @return string
     */
    protected function getURI(){
		return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	}

    /**
     * @return string
     */
    protected function getHTTPReferer(){
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	}

    /**
     * @return string
     */
    protected function getUserAgent(){
		return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	}

    /**
     * @param $message
     * @param $status
     * @param string|null $redirect
     * @param array|null $data
     * @param bool $ignoreajax
     * @return array|string
     */
    protected function msg($message,$status,$redirect = null,$data = null,$ignoreajax = false){
		$msg_data = array('message' => $message, 'status' => $status, 'redirect' => $redirect);
		if (!is_null($data)){
			$msg_data = is_array($data) ? array_merge($msg_data,$data) : array_merge($msg_data,array('html' => $data));
		}
		return OPAL_Portal::env('ajax',false) && !$ignoreajax ? $msg_data : $this->templater->fetch('message.phtml',$msg_data);
	}

    /**
     * @param string $url
     * @param bool $permanent
     */
    protected function redirect($url,$permanent = false){
		header($permanent ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 302 Found');
		header('Location: '.$url);
		die();
	}

    /**
     * @param $message
     * @param array $vars
     * @param string $log_name
     * @param int $status
     * @param \Orange\Database\ActiveRecord|null $object
     */
    protected function log($message,$vars = array(),$log_name = 'LOG_MISC',$status = self::STATUS_INFO,$object = null){
		$log = new OPAM_Log();
		$log->set('log_log', $log_name);
		$log->set('log_status', $status);
		$log->set('log_time', time());
		$log->set('log_uri', $this->getURI());
		$log->set('log_ip', $this->getIP());
		$log->set('log_useragent', $this->getUserAgent());
		$log->set('log_user_id', $this->user ? $this->user->id : 0);
		$log->set('log_classname', is_object($object) ? get_class($object) : '');
		$log->set('log_object_id', is_object($object) ? $object->id : 0);
		$log->set('log_message', $message);
		$log->set('log_vars', $vars);
		if (!$log->save() || ($status == self::STATUS_ALERT)){
			$log->send(OPAL_Portal::config('system_email_system','webmaster@'.OPAL_Portal::config('domain','localhost')));
		}
	}

    /**
     * @param string $methodname
     * @return bool
     */
    public function isMethodCacheable($methodname){
		return isset($this->cachemap[$methodname]) && ((strpos($methodname,'Block') !== false) || !$this->getPostArray());
	}

    /**
     * @param string $methodname
     * @param array $request
     * @return string
     */
    private function getMethodFileName($methodname, $request){
		if (isset($this->cachemap[$methodname])){
			$map = $this->cachemap[$methodname];
			$file = 'tmp/cache/methods/'.get_class($this).'/'.$methodname;
			if (in_array('id_is_page_id',$map)){
				$file .= '/'.OPAL_Portal::getInstance()->content->id;
			}
			if (in_array('id_is_first_argument',$map)){
				$file .= '/'.(isset($request[0]) ? intval($request[0]) : 0);
			}
			if (isset($map['id_is_arg'])){
				$file .= '/'.intval($this->arg($map['id_is_arg'],0));
			}
			$file .= '/';
			if (in_array('by_user_access',$map)){
				$file .= intval($this->user->get('user_status')).'-US_';
				$file .= implode('-',$this->user->get('user_groups')).'-UG_';
			}
			if (in_array('by_user_id',$map)){
				$file .= $this->user->id.'-UI_';
			}
			if (in_array('by_content_id',$map)){
				$file .= $this->content->id.'-CI_';
			}
			if (in_array('by_page_id',$map)){
				$file .= OPAL_Portal::getInstance()->content->id.'-PI_';
			}
			if (in_array('by_date',$map)){
				$file .= date("Ymd");
			}
			if ($request){
				$file .= md5(implode(';',$request)).'-RP_';
			}
			if ($this->args){
				$file .= md5(http_build_query($this->args)).'-CA_';
			}
			$file .= ( $this->content->get('content_lang') ? $this->content->get('content_lang') : 'xx' ).'-'.OPAL_Portal::env('protocol').'.html';
			return $file;
		} else {
			return '';
		}
	}

    /**
     * @param string $methodname
     * @param array $request
     * @return string|null
     */
    public function getMethodCache($methodname,$request){
		if ($filename = $this->getMethodFileName($methodname, $request)){
			$file = new OPAL_File($filename);
			return $file->getData();
		} else {
			return null;
		}
	}

    /**
     * @param string $methodname
     * @param array $request
     * @param string $data
     * @return bool
     */
    public function setMethodCache($methodname,$request,$data){
		if ($filename = $this->getMethodFileName($methodname, $request)){
			$file = new OPAL_File($filename);
			if (!($status = $file->saveData($data))){
				$this->log('CACHE_NOT_SAVED %s',array($filename),'LOG_CACHE',self::STATUS_ALERT);
			}
			return (bool)$status;
		} else {
			return false;
		}
	}

    /**
     * @param string|null $classname
     * @param string|null $methodname
     * @param int|null $id
     */
    public function deleteMethodCache($classname = null,$methodname = null,$id = null){
		$path = 'tmp/cache/methods';
		$path .= !is_null($classname) ? '/'.$classname : '/'.get_class($this);
		$path .= !is_null($methodname) ? '/'.$methodname : '';
		$path .= !is_null($id) ? '/'.intval($id) : '';
		$dir = new OPAL_File($path);
		$dir->delete();
	}

    /**
     * @param string $message
     * @param array $vars
     */
    public function alert($message,$vars = array()){
		$this->log($message,$vars,'LOG_SYSTEM',self::STATUS_ALERT);
	}
	
	
}