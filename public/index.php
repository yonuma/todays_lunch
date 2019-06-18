<?php
use LINE\LINEBot\EchoBot\dependencies;
use LINE\LINEBot\EchoBot\routes;
use LINE\LINEBot\EchoBot\setting;
require_once __DIR__ . '/../vendor/autoload.php';
$setting = setting::getSetting();
$app = new Slim\App($setting);
(new dependencies())->register($app);
(new routes())->register($app);
$app->run();