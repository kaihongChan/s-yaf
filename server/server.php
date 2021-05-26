<?php

ini_set('memory_limit', '2048M');
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__));
define('CONF_PATH', ROOT_PATH . DS . 'conf');
define('APPLICATION_PATH', ROOT_PATH . DS . 'application');
define('LIBRARY_PATH', APPLICATION_PATH . DS . 'library');

require LIBRARY_PATH . DS . 'HttpServer.php';

$serverObj = HttpServer::getInstance();
$serverObj->setServerConfigIni(CONF_PATH . DS . 'server.ini');
$serverObj->setAppConfigIni(CONF_PATH . DS . 'application.ini');
$serverObj->start();