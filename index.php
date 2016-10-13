<?php
//TODO Request to module/... show not found as 404
//TODO Check content_type status in OPAM_Content::getList, or...
//TODO Exclude page from Google directive (implement for search, etc)
error_reporting(E_ALL);
define('OP_SYS_ROOT',__DIR__ . '/');
require_once OP_SYS_ROOT.'core/autoloader.php';
try {
    echo OPAL_Portal::getInstance()->execute();
} catch (Exception $e){
    OPAL_Portal::getInstance()->processException($e);
}