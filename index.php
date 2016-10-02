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
    if (!is_null($webmaster_email = OPAL_Portal::getWebmasterEmailForException())){
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
        $message = 'URL: ' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] . "\n\n" . $e->getMessage() . "\n\n" . $e->getTraceAsString();
        if ($e instanceof \Orange\FS\FSException) {
            $message .= "\n\nFile: " . $e->getFilepath();
        }
        if ($webmaster_email === '#'){
            header('Content-type: text/plain');
            echo $message;
        } else {
            mail($webmaster_email, 'Exception on site '.$_SERVER['HTTP_HOST'], $message);
        }
    }
    die();
}