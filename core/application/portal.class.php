<?php

/**
 * Front controller. Singletone.
 * @author Mikhail Kelner
 */
class OPAL_Portal {
	
	/**
	 * Current language of the website
	 * @static
	 * @var string
	 */
	public static $sitelang = 'en';
	
	/**
	 * @static
	 * @var array
	 */
	private static $configs = array();
	
	/**
	 * @static
	 * @var array
	 */
	private static $enviroment = array();
	
	/**
	 * @static
	 * @var array
	 */
	private static $hooks = array();
	
	/**
	 * Unique object of class OPAL_Portal
	 * @static
	 * @var OPAL_Portal
	 */
	private static $portal;
	
	/**
	 * Requested content object
	 * @var OPAM_Content
	 */
	public $content;

	/**
	 * @var OPAM_User
	 */
	public $user;

    /**
     * @var OPAL_SessionInterface
     */
    public $session;
	
	/**
	 * @var string
	 */
	public $data_type = 'text/html';
	
	/**
	 * @var OPAL_Templater
	 */
	public $templater;
	
	/**
	 * Parsed request path
	 * @var array
	 */
	private $request = array();
	
	/**
	 * Name of file from folder /themes/yourtheme/ with name like main-html.phtml
	 * @var string
	 */
	private $main_template = '';
	
	/**
	 * HTML code generated by blocks, grouped by area
	 * @var array
	 */
	private $areas_data = array();
	
	/**
	 * List of enabled modules (codes)
	 * @var OPAL_Module[]
	 */
	private $modules = array();
	
	/**
	 * If there is no installed modules - go to install mode
	 * @var boolean
	 */
	private $install_mode = false;
	
	/**
	 * getInstance method for Singletone pattern
	 * @return OPAL_Portal
	 */
	public static function getInstance(){
		if (is_null(self::$portal)){
			self::$portal = new OPAL_Portal();
			self::$portal->init();
		}
		return self::$portal;
	}

	/**
	 * Class constructor
	 */
	private function __construct(){
	}

	/**
	 * Class destructor
	 */
	public function __destruct(){
		if (!$this->install_mode){
			$this->session->close();
		}
		$this->echoDebugData();
	}

	/**
	 * Init system
	 */
	private function init(){
		mb_internal_encoding("UTF-8");
		$this->initEnvironment();
		$this->loadConfig();
		define('OP_WWW', self::env('protocol') . '://' . self::config('system_domain',$_SERVER["HTTP_HOST"]) . (($bdir = self::config('system_base_dir',trim($_SERVER["REQUEST_URI"],'/'))) ? '/'.$bdir : ''));
        $sessionclass = $this->config('sessionclass','OPAL_Session');
        $this->session = new $sessionclass();
        $this->templater = new OPAL_Templater(self::config('system_theme'));
		self::$sitelang = isset($_GET['lang']) && (strlen(trim($_GET['lang'])) == 2) ? trim($_GET['lang']) : self::config('system_default_lang',self::$sitelang);
		if (!$this->install_mode){
			$this->initModules();
			$this->initUser();
		}
	}

	/**
	 * Set enviroment data into property $enviroment
	 */
	private function initEnvironment(){
		if (self::$enviroment['ajax'] = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))){
			$this->data_type = 'application/json';
		}
		if (self::$enviroment['cli'] = (isset($_SERVER['argv']) && is_array($_SERVER['argv']) && (count($_SERVER['argv']) > 1))){
			if ($url = $_SERVER['argv'][1]){
				$url = parse_url($url);
				$_SERVER['HTTP_HOST'] = isset($url['host']) ? $url['host'] : 'localhost';
				$_SERVER['REQUEST_URI'] = isset($url['path']) ? $url['path'] : '/';
			}
			$this->data_type = 'plain/text';
		}
		self::$enviroment['protocol'] = (!empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] != 'off')) ? 'https' : 'http';
		self::$enviroment['hostname'] = $_SERVER['HTTP_HOST'];
		self::$enviroment['request'] = $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Set config into property $configs
	 */
	private function loadConfig(){
		$config = array();
		$installed = false;
		if (is_file($filename = OP_SYS_ROOT.'config/default.php')){
			require_once $filename;
			$installed = true;
		}
		if (is_file($filename = OP_SYS_ROOT.'config/'.self::$enviroment['hostname'].'.php')){
			require_once $filename;
			$installed = true;
		}
		if ($installed){
			self::$configs = $config;
			if ($config = OPAM_Config::loadActive()){
				self::$configs = array_merge($config,self::$configs);
			}
		} else {
			$this->install_mode = true;
		}
	}

	/**
	 * Load and init enabled modules
	 */
	private function initModules(){
		if ($modules = OPAM_Module::getModules(true)){
			foreach ($modules as $module){
				$module->init();
				$this->modules[$module->get('module_code')] = $module;
			}
		} else {
			$this->install_mode = true;
		}
	}

    /**
     * @param string $event
     * @return array
     */
    public function processHooks($event){
		$result = array();
		if (!empty(self::$hooks[$event])){
			foreach (self::$hooks[$event] as $i => $hook){
				$module = explode('_', $hook[0]);
				$module = strtolower($module[1]);
                $classname = $hook[0];
				$methodname = $hook[1].'Hook';
				list($commandResult,$lastExecuteContentStatus) = $this->callMethod($module,$classname,$methodname,$hook[2]);
				if (!is_null($commandResult)){
					$result[] = $commandResult;
				}
			}
		}
		return $result;
	}
	
	/**
	 * Init session, load user
	 */
	private function initUser(){
		if (self::env('cli',false)){
			$this->user = new OPAM_User(isset($_SERVER['argv'][2]) ? intval($_SERVER['argv'][2]) : null);
		} else {
			if ($this->session->cookieExists() && !is_null($this->session->get('uid'))){
				$this->user = new OPAM_User(intval($this->session->get('uid')));
			} else {
				$this->processHooks('initUser_noUID');
				if ($this->user && $this->user->id){
                    $this->session->set('uid',$this->user->id);
				} else {
					$this->user = new OPAM_User();
				}
			}
		}
	}
	
	/**
	 * Set properties $request and $sitelang
	 */
	private function initRequestURI(){
		$u = $_SERVER['REQUEST_URI'];
		$u = explode('?', $u);
		if (isset($u[1])){
			parse_str($u[1],$_GET);
		}
		$u = trim($u[0],'/');
		if (!empty(self::config('system_base_dir')) && (strpos($u, self::config('system_base_dir')) === 0)){
			$u = trim(substr($u, strlen(self::config('system_base_dir'))),'/');
		}
		$request = !empty($u) ? explode('/',$u) : array();
		if (!empty($request[0]) && in_array($request[0], self::config('system_enabled_langs',array()))){
			self::$sitelang = array_shift($request);
		
		}
        $alias = array(
            'sitemap.xml' => array('module','system','system','sitemap'),
        );
		$this->request = isset($alias[implode('/',$request)]) ? $alias[implode('/',$request)] : $request;
	}
	
	public function execute(){
		$this->initRequestURI();
		$response = $this->processPage();
		$admin_panel = $this->content->get('content_type') == 'admin';
		header('Content-Type: '.$this->data_type.'; charset=utf-8');
		if (($this->data_type == 'text/html')){
			if (!$this->install_mode){
				$this->processBlocks($admin_panel);
			}
			return $this->templater->fetch($this->main_template,array(
				'portal'   => $this,
				'content'  => $this->content,
				'response' => $response,
			));
		} else {
			return $response;
		}
	}
	
	public function getRequest(){
		return $this->request;
	}
	
	private function processPage(){
        OPAL_Lang::load('modules/system/lang', self::$sitelang);
        if (!empty($this->request[0]) && ($this->request[0] == 'admin')){ //TODO Add here install mode too
            OPAL_Lang::load('modules/system/lang/admin', self::$sitelang);
        }
        if (!$this->install_mode){
			if (!empty($this->request[0]) && ($this->request[0] == 'module') && (count($this->request) >= 4)){
				list($status,$output) = $this->processContentDirect();
			} else {
				list($status,$output) = $this->processContentRegular();
			}
			if ($status == 'not-found'){
				header('HTTP/1.0 404 Not Found');
				$this->content = OPAM_Content::getContent('error','','error/not-found');
                $this->content->set('content_title',OPAL_Lang::t($this->content->get('content_title')));
				$output = $this->executeContent($this->content);
			} else if ($status == 'unauthorized'){
				header('HTTP/1.0 401 Unauthorized');
				$this->content = OPAM_Content::getContent('error','','error/unauthorized');
                $this->content->set('content_title',OPAL_Lang::t($this->content->get('content_title')));
				$output = $this->executeContent($this->content);
			}
			$this->main_template = $this->content->get('content_template');
		} else {
			$output = $this->install();
			$this->main_template = 'main-installer.phtml';
		}
		return $output;
	}
	
	private function processContentRegular(){
		if (empty($this->request[0])){
			$this->content = OPAM_Page::getHomepage(self::$sitelang);
		} else {
			if ($this->request[0] == 'admin'){
				$slug = 'admin/'.(!empty($this->request[1]) ? $this->request[1] : 'center');
				array_shift($this->request);
				$this->content = OPAM_Content::getContent('admin','',$slug);
			} else {
				$slug = $this->request[0];
				$this->content = OPAM_Content::getContent(null,self::$sitelang,$slug);
			}
		}
		if (strpos($this->content->get('content_template'),'main-') === false){
			$status = 'not-found';
		}  else if (!$this->content->isAllowedForGroups($this->user->get('user_groups'))){
			$status = 'unauthorized';
		} else {
			$status = 'found';
		}
		return array($status,$this->executeContent($this->content));
	}
	
	private function processContentDirect(){
		array_shift($this->request);
		$module = array_shift($this->request);
		$this->content = new OPAM_Page();
		$this->content->set('content_title',OPAL_Lang::t('MODULE_'.strtoupper($module)));
		$this->content->set('content_type','module');
		$this->content->set('content_slug',implode('/', $this->request));
		$this->content->set('content_lang',self::$sitelang);
		$this->content->set('content_status',6);
		$this->content->set('content_commands',array(
			array( 'module' => $module, 'controller' => $this->request[0], 'method' => $this->request[1], 'static' => false, 'args' => array() ),
		));
		$this->content->set('content_template','main-html.phtml');
		$output = $this->executeContent($this->content);
		return array($this->lastExecuteContentStatus,$output);
	}
	
	public function blockDirect($request,$args = array()){
		$request = explode('/',$request);
		$module = array_shift($request);
		$block = new OPAM_Block();
		$block->set('content_title',OPAL_Lang::t('MODULE_'.strtoupper($module)));
		$block->set('content_type','module');
		$block->set('content_slug',implode('/', $request));
		$block->set('content_lang',self::$sitelang);
		$block->set('content_status',6);
		$block->set('content_commands',array(
			array( 'module' => $module, 'controller' => $request[0], 'method' => $request[1], 'static' => true, 'args' => $args ),
		));
		$output = $this->executeContent($block);
		return $output;
	}
	
	private function install(){
		$system = new OPMO_System(null);
		$form = $system->getInstallForm();
		if (count($_POST) == 0){
			$response = $form->getHTML($this->templater);
		} else {
			$errors = $form->setValues(null,true);
			if (!$errors){
				$params = $form->getValues();
				$params['domain'] = $_SERVER["HTTP_HOST"];
				$params['base_dir'] = trim($_SERVER["REQUEST_URI"],'/');
				$errors = $system->install($params);
				if (is_null($errors)){
					$errors['go'] = OPAL_Lang::t('Portal was installed earlier');
				}
				if ($errors){
					foreach ($errors as $key => $error){
						$form->setError($key, $error);
					}
				}
			}
			$errors = $form->getErrors();
			if (self::env('ajax')){
				$response = json_encode($errors);
			} else {
				if ($errors){
					$response = $form->getHTML($this->templater);
				} else {
					header('Location: '.OP_WWW);
					die();
				}
			}
		}
		$this->content = new OPAM_Content();
		$this->content->set('content_title', OPAL_Lang::t('INSTALLER'));
		return $response;
	}

    /**
     * @param bool|false $admin_panel
     */
    private function processBlocks($admin_panel = false){
		$areas = $admin_panel ? $this->templater->theme->getAdminAreas() : $this->templater->theme->getThemeAreas();
		$blocks = OPAM_Block::getBlocksByAreas(array_keys($areas), $admin_panel ? '' : self::$sitelang, null, $this->content, $this->user, true);
		foreach ($blocks as $area => $area_blocks){
			if (!isset($this->areas_data[$area])){
				$this->areas_data[$area] = array();
			}
			foreach ($area_blocks as $content){
				$this->areas_data[$area][] = array($content,$this->executeContent($content));
			}
		}
	}
	
	public function blocksArea($area,$preHTML = '',$postHTML = '',$template = 'area-default.phtml'){
		return $this->templater->fetch($template,array(
			'blocks'   => isset($this->areas_data[$area]) ? $this->areas_data[$area] : array(),
			'preHTML'  => $preHTML,
			'postHTML' => $postHTML,
		));
	}
	
	//TODO PHP7 Adaptation
	
	private $lastExecuteContentStatus = '';

    /**
     * @param OPAM_Content $content
     * @return array|string
     */
    private function executeContent($content){
		$result = array();
		if ($commands = $content->get('content_commands')){
			$isDirect = $content->get('content_type') == 'module';
			$isCli = self::env('cli',false);
			foreach ($commands as $command){
				if (isset($this->modules[$command['module']])){
					$classname = $this->getCommandClassName($command);
					$methodname = $this->getCommandMethodName($command,!empty($this->request[1]) ? $this->request[1] : 'index',self::env('ajax',false),$isDirect,$isCli);
					$request = !$command['static'] && count($this->request) >= 2 ? array_slice($this->request, 2) : array();
					list($commandResult,$this->lastExecuteContentStatus) = $this->callMethod($command['module'],$classname,$methodname,$request,$command['args'],$content);
                    if (!is_null($commandResult)){
						$result[] = $commandResult;
					}
				} else {
					$this->lastExecuteContentStatus = 'not-found';
                    $controller = new OPAL_Controller($content,$this->user,$this->session,$this->templater,array());
					$controller->alert('PORTAL_MODULE_NOT_FOUND');
				}
			}
		}
		$response = '';
		if ($result){
			if ($this->data_type == 'application/json'){
				$response = array('html' => '', 'status' => 999);
				foreach ($result as $res){
					if (is_array($res)){
						foreach ($res as $key => $value){
							if ($key == 'html'){
								$response[$key] .= $value;
							} else if ($key == 'status'){
								$response[$key] = min($response[$key],$value);
							} else {
								$response[$key] = $value;
							}
						}
					} else {
						$response['html'] .= $res;
					}
				}
				$response = json_encode($response);
			} else {
				$response = implode('', $result);
			}
		}
		return $response;
	}
	
	private function callMethod($module,$classname,$methodname,$request,$arguments = array(),$content = null){
		$result = null;
		if ($requiredPrivilege = $this->modules[$module]->getPrivilege($classname,$methodname)){
            $privilegeCheck = OPAM_Privilege::hasPrivilege($requiredPrivilege, $this->user);
		} else {
			$privilegeCheck = true;
		}
		if ($privilegeCheck){
			if (class_exists($classname)){
                /** @var OPAL_Controller $controller */
                $controller = new $classname($content,$this->user,$this->session,$this->templater,$arguments);
				$controllerReflection = new ReflectionClass($controller);
				try {
					$methodReflection = $controllerReflection->getMethod($methodname);
					$cache_loaded = false;
					$method_result = '';
					$is_method_cacheable = self::config('system_cache_method', false) && $controller->isMethodCacheable($methodname);
					if ($is_method_cacheable) {
						$method_result = $controller->getMethodCache($methodname, $request);
						if (!is_null($method_result)) {
							$cache_loaded = true;
						}
					}
					if (!$cache_loaded) {
						OPAL_Lang::load('modules/' . $module . '/lang', self::$sitelang);
                        if (!empty($this->request[0]) && ($this->request[0] == 'admin')){
                            OPAL_Lang::load('modules/' . $module . '/lang/admin', self::$sitelang);
                        }
						$method_result = $methodReflection->invokeArgs($controller, $request);
						if ($is_method_cacheable) {
							$controller->setMethodCache($methodname, $request, $method_result);
						}
					}
					$result = $method_result;
					$execStatus = 'success';
				} catch (ReflectionException $e){
					$execStatus = 'not-found';
				}
			} else {
				$execStatus = 'not-found';
				$controller = new OPAL_Controller($content,$this->user,$this->session,$this->templater,array());
				$controller->alert('PORTAL_%s_CONTROLLER_NOT_FOUND',array($classname));
			}
		} else {
			$execStatus = 'unauthorized';
		}
		return array($result,$execStatus);
	}
	
	private function getCommandClassName($command){
		$classname = '';
		if ($command['module'] || $command['controller']){
			$classname = $command['module'] ? 'OPM' : 'OPA';
			$classname .= (strpos($command['controller'], 'admin-') === 0) ? 'A' : 'C';
			if ($command['module']){
				$classname .= '_'.ucfirst($command['module']);
			}
			if ($command['controller'] && ($command['module'] != $command['controller'])){
				$controller = $command['controller'];
				if ((strpos($controller, 'admin-') === 0)){
					$controller = substr($controller,6);
				}
				$controller = '/'.$controller;
				while (($s = strpos($controller,'/')) !== false){
					$controller{$s} = '_';
					$controller{$s+1} = strtoupper($controller{$s+1});
				}
				$classname .= $controller;
			}
		}
		return $classname;
	}
	
	private function getCommandMethodName($command,$urlAction = '',$isAjax = false,$isDirect = false,$isCli = false){
		$method = '';
		if ($command['static']){
			$method .= $command['method'] ? $command['method'] : 'index';
			$method .= 'Block';
		} else {
			$method .= $command['method'] ? $command['method'] : $urlAction;
			$method .= $isCli ? 'Cli' : ($isAjax ? 'Ajax' : 'Action');
		}
		if ($isDirect){
			$method .= 'Direct';
		}
		return $method;
	}

	private function echoDebugData(){
		$response = '';
		if (self::config('system_debug')){
			if ($this->data_type == 'text/html'){
				$response = '<!-- Generate time: '.sprintf("%.4f",microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']).' sec | Memory usage: '.sprintf("%.2f", memory_get_usage()/1048576).' MB | Peak memory usage: '.sprintf("%.2f", memory_get_peak_usage()/1048576).' MB -->';
			}
		}
		echo $response;
	}
	
	public static function config($param,$default = null){
		return isset(self::$configs[$param]) ? self::$configs[$param] : $default;
	}

	public static function env($param,$default = null){
		return isset(self::$enviroment[$param]) ? self::$enviroment[$param] : $default;
	}
	
	public static function addHook($event,$class,$method,$args = array()){
		if (!isset(self::$hooks[$event])){
			self::$hooks[$event] = array();
		}
		self::$hooks[$event][] = array($class,$method,$args);
	}
	
}