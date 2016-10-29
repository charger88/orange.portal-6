<?php

/**
 * Class OPAL_Controller
 */
class OPAL_Controller
{

	use OPAL_Request;

	const STATUS_ALERT = -1;
	const STATUS_ERROR = 0;
	const STATUS_WARNING = 1;
	const STATUS_NOTFOUND = 2;
	const STATUS_INFO = 3;
	const STATUS_OK = 4;
	const STATUS_COMPLETE = 5;

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
	protected $args = [];

	/**
	 * @var array
	 */
	protected $cachemap = [];

	/**
	 * @param OPAM_Content $content
	 * @param OPAM_User $user
	 * @param OPAL_Templater $templater
	 * @param array $args
	 */
	public function __construct($content, $user, $session, $templater, $args = [])
	{
		$this->content = $content;
		$this->user = $user;
		$this->session = $session;
		$this->templater = $templater;
		$this->args = $args;
		foreach ($this->args as $key => $value) {
			if ($value && ($value{0} == '@')) {
				$this->args[$key] = $this->content->get(substr($value, 1));
			}
		}
	}

	/**
	 * @param string $param
	 * @param string | boolean | integer | float $default
	 * @return string | boolean | integer | float
	 */
	protected function arg($param, $default = null)
	{
		return isset($this->args[$param]) ? $this->args[$param] : $default;
	}

	/**
	 * @param string $template
	 * @param string $html
	 * @param array $data
	 * @return string|null
	 */
	protected function wrapContentWithTemplate($template, $html = '', $data = [])
	{
		$data['html'] = $html;
		return $this->templater->fetch($template, $data);
	}

	/**
	 * @param $message
	 * @param $status
	 * @param string|null $redirect
	 * @param array|null $data
	 * @param bool $ignoreajax
	 * @return array|string
	 */
	protected function msg($message, $status, $redirect = null, $data = null, $ignoreajax = false)
	{
		$msg_data = ['message' => $message, 'status' => $status, 'redirect' => $redirect];
		if (!is_null($data)) {
			$msg_data = is_array($data) ? array_merge($msg_data, $data) : array_merge($msg_data, ['html' => $data]);
		}
		if (OPAL_Portal::env('cli', false)) {
			return $msg_data['message'];
		} else {
			return OPAL_Portal::env('ajax', false) && !$ignoreajax ? $msg_data : $this->templater->fetch('message.phtml', $msg_data);
		}
	}

	/**
	 * @param string $url
	 * @param bool $permanent
	 */
	protected function redirect($url, $permanent = false)
	{
		header($this->getServer('SERVER_PROTOCOL') . ($permanent ? ' 301 Moved Permanently' : ' 302 Found'));
		header('Location: ' . $url);
		die();
	}

	/**
	 * @param $message
	 * @param array $vars
	 * @param string $log_name
	 * @param int $status
	 * @param \Orange\Database\ActiveRecord|null $object
	 */
	protected function log($message, $vars = [], $log_name = 'LOG_MISC', $status = self::STATUS_INFO, $object = null)
	{
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
		if (!$log->save() || ($status == self::STATUS_ALERT)) {
			$log->send(OPAL_Portal::config('system_email_system', 'webmaster@' . OPAL_Portal::config('domain', 'localhost')));
		}
	}

	/**
	 * @param string $methodname
	 * @return bool
	 */
	public function isMethodCacheable($methodname)
	{
		return isset($this->cachemap[$methodname]) && ((strpos($methodname, 'Block') !== false) || !$this->getPostArray());
	}

	/**
	 * @param string $methodname
	 * @param array $request
	 * @return string
	 */
	private function getMethodFileName($methodname, $request)
	{
		if (isset($this->cachemap[$methodname])) {
			$map = $this->cachemap[$methodname];
			$file = 'sites/' . OPAL_Portal::$sitecode . '/tmp/cache/methods/' . get_class($this) . '/' . $methodname;
			if (in_array('id_is_page_id', $map)) {
				$file .= '/' . OPAL_Portal::getInstance()->content->id;
			}
			if (in_array('id_is_first_argument', $map)) {
				$file .= '/' . (isset($request[0]) ? intval($request[0]) : 0);
			}
			if (isset($map['id_is_arg'])) {
				$file .= '/' . intval($this->arg($map['id_is_arg'], 0));
			}
			$file .= '/';
			if (in_array('by_user_access', $map)) {
				$file .= intval($this->user->get('user_status')) . '-US_';
				$file .= implode('-', $this->user->get('user_groups')) . '-UG_';
			}
			if (in_array('by_user_id', $map)) {
				$file .= $this->user->id . '-UI_';
			}
			if (in_array('by_content_id', $map)) {
				$file .= $this->content->id . '-CI_';
			}
			if (in_array('by_page_id', $map)) {
				$file .= OPAL_Portal::getInstance()->content->id . '-PI_';
			}
			if (in_array('by_date', $map)) {
				$file .= date("Ymd");
			}
			if ($request) {
				$file .= md5(implode(';', $request)) . '-RP_';
			}
			if ($this->args) {
				$file .= md5(http_build_query($this->args)) . '-CA_';
			}
			$file .= ($this->content->get('content_lang') ? $this->content->get('content_lang') : 'xx') . '-' . OPAL_Portal::env('protocol') . '.html';
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
	public function getMethodCache($methodname, $request)
	{
		if ($filename = $this->getMethodFileName($methodname, $request)) {
			$file = new \Orange\FS\File($filename);
			return $file->exists() ? $file->getData() : null;
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
	public function setMethodCache($methodname, $request, $data)
	{
		if ($filename = $this->getMethodFileName($methodname, $request)) {
			$file = new \Orange\FS\File($filename);
			if (!($status = $file->save($data))) {
				$this->log('CACHE_NOT_SAVED %s', array($filename), 'LOG_CACHE', self::STATUS_ALERT);
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
	public function deleteMethodCache($classname = null, $methodname = null, $id = null)
	{
		$path = 'sites/' . OPAL_Portal::$sitecode . '/tmp/cache/methods';
		$path .= !is_null($classname) ? '/' . $classname : '/' . get_class($this);
		$path .= !is_null($methodname) ? '/' . $methodname : '';
		$path .= !is_null($id) ? '/' . intval($id) : '';
		try {
			$dir = \Orange\FS\FS::open($path);
			if ($dir->exists()) {
				$dir->remove();
			}
		} catch (\Exception $e) {
		}
	}

	/**
	 * @param string $message
	 * @param array $vars
	 */
	public function alert($message, $vars = [])
	{
		$this->log($message, $vars, 'LOG_SYSTEM', self::STATUS_ALERT);
	}


}