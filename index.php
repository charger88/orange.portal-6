<?php
error_reporting(E_ALL);
error_reporting(-1);
define('OP_SYS_ROOT',__DIR__ . '/');
require_once OP_SYS_ROOT.'core/autoloader.php';
$portal = OPAL_Portal::getInstance();
echo $portal->execute();