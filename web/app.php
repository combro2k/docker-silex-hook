<?php
/** @var Combro2k\Hook\Application $app */
$root = dirname(__DIR__);
$app = require_once $root.'/src/Combro2k/app.php';
$app->offsetSet('rootPath', $root);
$app->run();