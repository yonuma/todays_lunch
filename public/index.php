<?php
use LINE\LINEBot\EchoBot\Dependency;
use LINE\LINEBot\EchoBot\Route;
use LINE\LINEBot\EchoBot\Setting;
require_once __DIR__ . '/../vendor/autoload.php';
$setting = setting::getSetting();
$app = new Slim\App($setting);
(new dependencies())->register($app);
(new routes())->register($app);
$app->run();