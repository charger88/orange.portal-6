<?php
//TODO Request to module/... show not found as 404
//TODO Check content_type status in OPAM_Content::getList, or...
//TODO Maybe set for getList default min_status... for security reasons
//TODO Exclude page from Google directive (implement for search, etc)
error_reporting(E_ALL);
define('OP_SYS_ROOT',__DIR__ . '/');
require_once OP_SYS_ROOT.'core/autoloader.php';
try {
    $portal = OPAL_Portal::getInstance();
    echo $portal->execute();
} catch (Exception $e){
    header('Content-type: text/plain');
    echo 'Orange.Portal uncaught exception: '.$e->getMessage()."\n";
    echo $e->getTraceAsString();
}