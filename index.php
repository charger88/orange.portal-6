<?php
//TODO Request to module/... show not found as 404
//TODO Check content_type status in OPAM_Content::getList, or...
error_reporting(E_ALL);
define('OP_SYS_ROOT',__DIR__ . '/');
require_once OP_SYS_ROOT.'core/autoloader.php';
$portal = OPAL_Portal::getInstance();
echo $portal->execute();