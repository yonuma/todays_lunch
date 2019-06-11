<?php
use Slim\Http\Request;
use Slim\Http\Response;

class IndexController
{
    public function index()
    {
        // Composerでインストールしたライブラリを一括読み込み
        require_once __DIR__ . '/vendor/autoload.php';

        // アクセストークンを使いCurlHTTPClientをインスタンス化
        $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(ACCESS_TOKEN);

        //CurlHTTPClientとシークレットを使いLINEBotをインスタンス化
        $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => CHANNEL_SECRET]);

        // LINE Messaging APIがリクエストに付与した署名を取得
        $signature = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

        //署名をチェックし、正当であればリクエストをパースし配列へ、不正であれば例外処理
        $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);

        foreach($events as $event){
            // 緯度
            // $latitude = $event->getLatitude();
            // // 経度
            // $longitude = $event->getLongitude();

            $response = $bot->replyMessage(
                $event->getReplyToken(), new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($event->getText())  
            );
        }
        

        
    }
}