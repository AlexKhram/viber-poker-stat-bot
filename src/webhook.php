<?php

namespace PokerBot;

use PokerBot\Lib\DotEnv;

require_once __DIR__ . '/Lib/DotEnv.php';

DotEnv::load( __DIR__ . '/../config/.env');

if (strpos($_SERVER['HTTP_USER_AGENT'], 'akka-http') !== 0) {
    $data = callApi('https://chatapi.viber.com/pa/get_account_info');
//    var_dump($data['members']);
    var_dump($data->members);
    var_dump('die');
    die();
}

$request = file_get_contents("php://input");
$input = json_decode($request, true);

$fp = fopen('webhook.log', 'ab');
fwrite($fp, date('y.m.d H:m:s:')
    . ($_SERVER ? 'Server: ' . json_encode($_SERVER) . PHP_EOL : '')
    . ($_REQUEST ? 'Request: ' . json_encode($_REQUEST) . PHP_EOL : '')
    . ($_POST ? 'Post: ' . json_encode($_POST) . PHP_EOL : '')
    . ($_GET ? 'Get: ' . json_encode($_GET) . PHP_EOL : '')
    . ($_GET ? 'Input: ' . $request . PHP_EOL : '')
    . PHP_EOL
);
fclose($fp);

if ($input['event'] == 'webhook') {
    $webhook_response['status'] = 0;
    $webhook_response['status_message'] = "ok";
    $webhook_response['event_types'] = 'delivered';
    echo json_encode($webhook_response);
    die;
} elseif ($input['event'] == "subscribed") {
    // when a user subscribes to the public account
} elseif ($input['event'] == "conversation_started") {
    // when a conversation is started
} elseif ($input['event'] == "message") {
    if ($input['message']['text'] === 'refresh_users') {

    }

    /* when a user message is received */
    $type = $input['message']['type']; //type of message received (text/picture)
    $text = $input['message']['text']; //actual message the user has sent
    $sender_id = $input['sender']['id']; //unique viber id of user who sent the message
    $sender_name = $input['sender']['name']; //name of the user who sent the message

    $data['receiver'] = $sender_id;
    $data['sender']['name'] = 'bot';
    $data['text'] = "The message to send to user";
    $data['type'] = 'text';
    $data['tracking_data'] = 'tracking_data';
    $data['min_api_version'] = 1;

    callApi('https://chatapi.viber.com/pa/send_message', $data);
}

function callApi(string $url, array $data = null)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', "X-Viber-Auth-Token: " . getenv('VIBER_AUTH_TOKEN')]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        $fp = fopen('webhook.log', 'ab');
        fwrite($fp, date('y.m.d H:m:s') . ': Error Resp: ' . json_encode(curl_getinfo($ch)));
        fclose($fp);
    }

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
//var_dump($response);
    return json_decode($response);
}

//"subscribed",
//"unsubscribed",
//"webhook",
//"conversation_started",
//"client_status",
//"action",
//"delivered",
//"failed",
//"message",
//"seen"
