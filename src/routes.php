<?php
namespace EchoBot;
use \GuzzleHttp\Client;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
class Route
{
    const RANGE = 2;
    const LUNCH = 1;
    const CARD  = 1;
    const HIT   = 10;
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
                $logger->info('Reply text: ' . $latitude . ':' . $longitude);
                // $lunchData = $this->getLunch($latitude, $longitude);
                // foreach($lunchData->rest as $storeData){
                //     $replyText .= $storeData->name;
                // }
                $replyText = $latitude . ':' . $longitude;
                $resp = $bot->replyText($event->getReplyToken(), $replyText);
                $logger->info($resp->getHTTPStatus() . ': ' . $resp->getRawBody());
            }
            $res->write('OK');
            return $res;
        });
    }

    public function getLunch($latitude, $longitude)
    {
        $client = new Client([
            'base_uri' => 'https://api.gnavi.co.jp/RestSearchAPI/v3/',
        ]);

        $method = 'GET';
        $access_key = getenv('GURUNAVI_ACCESS_KEY');
        $uri = '?keyid='.$access_key.'&latitude='.$latitude.'&longitude='.$longitude.'&range='.self::RANGE.'&lunch='.self::LUNCH.'&card='.self::CARD.'&hit_per_page='.self::HIT;
        $options = [];
        $response = $client->request($method, $uri, $options);
        $list = json_decode($response->getBody()->getContents(), true);

        return $this->response($list);
    }
}