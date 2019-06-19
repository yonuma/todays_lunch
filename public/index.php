<?php
use EchoBot\Dependency;
use EchoBot\Route;
use EchoBot\Setting;
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/settings.php';
require_once __DIR__ . '/../src/dependencies.php';
require_once __DIR__ . '/../src/routes.php';
$setting = Setting::getSetting();
$app = new Slim\App($setting);
(new Dependency())->register($app);
(new Route())->register($app);
$app->run();