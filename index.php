<?php
error_reporting(E_ALL);
define('OP_SYS_ROOT', __DIR__ . '/');
require_once OP_SYS_ROOT . 'autoloader.php';
try {
	echo \Orange\Portal\Core\App\Portal::getInstance()->execute();
} catch (\Exception $e) {
	\Orange\Portal\Core\App\Portal::getInstance()->processException($e);
}