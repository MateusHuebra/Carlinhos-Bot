<?php

namespace App\Services;

use App\Models\Reply;
use TelegramBot\Api\BotApi;

class TelegramAPI {
    
    private $telegram;

    public function __construct() {
        $this->telegram = new BotApi(env(TELEGRAM_API_TOKEN));
    }

    public function send(string $response, Reply $reply, array $update) {
        if($reply->type==='message') {
            $this->sendMessage($response, $update);
        } else if($reply->type==='sticker') {

        }
    }

    private function sendMessage(string $response, array $update) {
        $this->telegram->sendChatAction($update['message']['chat']['id'], 'typing');
        $length = strlen($response);
        $delay = $length * 200000;
        if($delay>5000000) {
            $delay = 5000000;
        }
        usleep($delay);
        
        $this->telegram->sendMessage(
            $update['message']['chat']['id'],
            $response,
            'MarkdownV2',
            true,
            $update['message']['message_id']
        );
    }

    public function get(string $method, array $update) {
        switch($method) {
            case 'chatMembersCount':
                $value = $this->telegram->getChatMembersCount($update['message']['chat']['id']);
                break;
            default:
                $value = '';
                break;
        }
        
        file_put_contents('php://stderr', "\n\n ".$method.': '.$value);
        return $value;
    }

}