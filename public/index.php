<?php

define('ROOT_PATH', dirname(__DIR__));
define('APPLICATION_PATH', ROOT_PATH . DS . 'application');
define('CONF_PATH', ROOT_PATH . DS . 'conf');

$app = new Yaf_Application(CONF_PATH . '/application.ini');
$app->bootstrap()->run();
