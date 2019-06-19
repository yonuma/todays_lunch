<?php

namespace EchoBot;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
class Dependency
{
    public function register(\Slim\App $app)
    {
        require_once __DIR__ . '/../const.php';
        $container = $app->getContainer();
        $container['logger'] = function ($c) {
            $settings = $c->get('settings')['logger'];
            $logger = new \Monolog\Logger($settings['name']);
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], \Monolog\Logger::DEBUG));
            return $logger;
        };
        $container['bot'] = function ($c) {
            $settings = $c->get('settings');
            $channelSecret = getenv('CHANNEL_SECRET');
            $channelToken = getenv('ACCESS_TOKEN');
            $bot = new LINEBot(new CurlHTTPClient($channelToken), [
                'channelSecret' => $channelSecret
            ]);
            return $bot;
        };
    }
}
