<?php
namespace EchoBot;
use \GuzzleHttp\Client;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
class Route
{
    const RANGE = 2;
    const LUNCH = 1;
    const CARD  = 1;
    const HIT   = 5;
    const GRNV_ACCESS_KEY = 'accfc6e85b4c25fa6710745bceb3a333';

    /**
     * 実行 function
     *
     * @param \Slim\App $app
     * @return void
     */
    public function register(\Slim\App $app)
    {
        $app->post('/callback', function (\Slim\Http\Request $req, \Slim\Http\Response $res) {
            /** @var \LINE\LINEBot $bot */
            $bot = $this->bot;
            /** @var \Monolog\Logger $logger */
            $logger = $this->logger;
            $signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
            if (empty($signature)) {
                return $res->withStatus(400, 'Bad Request');
            }
            // Check request with signature and parse request
            try {
                $events = $bot->parseEventRequest($req->getBody(), $signature[0]);
            } catch (InvalidSignatureException $e) {
                return $res->withStatus(400, 'Invalid signature');
            } catch (InvalidEventRequestException $e) {
                return $res->withStatus(400, "Invalid event request");
            }
            foreach ($events as $event) {
                if (!($event instanceof MessageEvent)) {
                    $logger->info('Non message event has come');
                    continue;
                }
                if (!($event instanceof LocationMessage)) {
                    $logger->info('Non location message has come');
                    continue;
                }
                $latitude = $event->getLatitude();
                $longitude = $event->getLongitude();
                // $list = $this->getLunch($latitude, $longitude);
                // $carousel_message = $this->makeCarousel($list);
                $client = new Client([
                    'base_uri' => 'https://api.gnavi.co.jp/RestSearchAPI/v3/',
                ]);
        
                $method = 'GET';
                $uri = '?keyid='.self::GRNV_ACCESS_KEY.'&latitude='.$latitude.'&longitude='.$longitude.'&range='.self::RANGE.'&lunch='.self::LUNCH.'&card='.self::CARD.'&hit_per_page='.self::HIT;
                $response = $client->request($method, $uri);
                $result = $response->getBody()->getContents();
                $list = json_decode($result, true);
                $columns = []; // カルーセル型カラムを5つ追加する配列
                foreach($list["rest"] as $storeData){
                    // カルーセルに付与するボタンを作る
                    $action = new UriTemplateActionBuilder("詳細を確認する", $storeData["url"]);
                    $name = mb_strimwidth($storeData["name"], 0, 35, "...", "UTF-8");
                    $pr = mb_strimwidth($storeData["pr"]["pr_short"], 0, 55, "...", "UTF-8");
                    // カルーセルのカラムを作成する
                    $column = new CarouselColumnTemplateBuilder($name, $pr, $storeData["image_url"]["shop_image1"], [$action]);
                    $columns[] = $column;
                }
                // カラムの配列を組み合わせてカルーセルを作成する
                $carousel = new CarouselTemplateBuilder($columns);
                // カルーセルを追加してメッセージを作る
                $carousel_message = new TemplateMessageBuilder("今日のランチ", $carousel);
                $message = new MultiMessageBuilder();
                $message->add($carousel_message);
                $resp = $bot->replyMessage($event->getReplyToken(), $message);
                $logger->info($resp->getHTTPStatus() . ': ' . $resp->getRawBody());
            }
            $res->write('OK');
            return $res;
        });
    }

    /**
     * カルーセル生成 function
     *
     * @param array $list
     * @return array
     */
    function makeCarousel($list)
    {
        $columns = []; // カルーセル型カラムを5つ追加する配列
        foreach($list["rest"] as $storeData){
            // カルーセルに付与するボタンを作る
            $action = new UriTemplateActionBuilder("詳細を確認する", $storeData["url"]);
            $name = mb_strimwidth($storeData["name"], 0, 35, "...", "UTF-8");
            $pr = mb_strimwidth($storeData["pr"]["pr_short"], 0, 55, "...", "UTF-8");
            // カルーセルのカラムを作成する
            $column = new CarouselColumnTemplateBuilder($name, $pr, $storeData["image_url"]["shop_image1"], [$action]);
            $columns[] = $column;
        }
        // カラムの配列を組み合わせてカルーセルを作成する
        $carousel = new CarouselTemplateBuilder($columns);
        // カルーセルを追加してメッセージを作る
        return $carousel_message = new TemplateMessageBuilder("今日のランチ", $carousel);
    }

    /**
     * ランチ情報取得 function
     *
     * @param string $latitude
     * @param string $longitude
     * @return array
     */
    function getLunch($latitude, $longitude)
    {
        $client = new Client([
            'base_uri' => 'https://api.gnavi.co.jp/RestSearchAPI/v3/',
        ]);

        $method = 'GET';
        $uri = '?keyid='.self::GRNV_ACCESS_KEY.'&latitude='.$latitude.'&longitude='.$longitude.'&range='.self::RANGE.'&lunch='.self::LUNCH.'&card='.self::CARD.'&hit_per_page='.self::HIT;
        $response = $client->request($method, $uri);
        $result = $response->getBody()->getContents();
        return $list = json_decode($result, true);
    }
}