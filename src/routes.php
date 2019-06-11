<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use \GuzzleHttp\Client;


    $app = new \Slim\App;
    $app->post('/', IndexController::class . ':index');
    $app->run();


