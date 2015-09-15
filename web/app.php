<?php
require_once(__DIR__.'/../vendor/autoload.php');

use Combro2k\Application;

$rootPath = dirname(__DIR__);
$app = new Application(array('rootPath' => $rootPath));
$app->run();