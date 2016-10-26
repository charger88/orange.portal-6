<?php
error_reporting(E_ALL);
define('OP_SYS_ROOT',__DIR__ . '/');
require_once OP_SYS_ROOT.'core/autoloader.php';
try {
    echo OPAL_Portal::getInstance()->execute();
} catch (Exception $e){
    OPAL_Portal::getInstance()->processException($e);
}