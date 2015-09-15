<?php
/** @var Combro2k\Hook\Application $app */
$rootDir = dirname(__DIR__);
$app = require_once($rootDir.'/src/Combro2k/app.php');
$app->run();