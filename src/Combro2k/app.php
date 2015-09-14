<?php

namespace Combro2k;

$rootPath = dirname(dirname(__DIR__));
require_once($rootPath.'/vendor/autoload.php');
$app = new Hook\Application(array('rootPath' => $rootPath));
return $app;