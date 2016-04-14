<?php
//TODO Request to module/... show not found as 404
//TODO Check content_type status in OPAM_Content::getList, or...
//TODO Exclude page from Google directive (implement for search, etc)
error_reporting(E_ALL);
define('OP_SYS_ROOT',__DIR__ . '/');
require_once OP_SYS_ROOT.'core/autoloader.php';
try {
    if (!empty($_GET['_rootfile'])){
        OPAL_Portal::outputRootFile($_GET['_rootfile']);
    } else {
        echo OPAL_Portal::getInstance()->execute();
    }
} catch (Exception $e){
    header('Content-type: text/plain');
    echo 'Orange.Portal uncaught exception: '.$e->getMessage()."\n";
    echo $e->getTraceAsString();
}