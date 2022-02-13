<?php

namespace App\Services;

use App\Models\Reply;
use TelegramBot\Api\BotApi;

class TelegramAPI {
    
    private $telegram;

    public function __construct() {
        $this->telegram = new BotApi(env('TELEGRAM_API_TOKEN'));
    }

    public function send(string $response, Reply $reply, array $update) {
        file_put_contents('php://stderr', "\n\n preparing to send");

        if($reply->type==='message') {
            $telegramResponse = $this->sendMessage($response, $update);
        } else if($reply->type==='sticker') {
            $telegramResponse = $this->sendSticker($response, $update);
        }
        
        file_put_contents('php://stderr', "\n sent");
    }

    private function sendMessage(string $response, array $update) {
        $this->telegram->sendChatAction($update['message']['chat']['id'], 'typing');
        $length = strlen($response);
        $delay = $length * 200000;
        if($delay>5000000) {
            $delay = 5000000;
        }
        usleep($delay);

        return $this->telegram->sendMessage(
            $update['message']['chat']['id'],
            $response,
            'MarkdownV2',
            true,
            $update['message']['message_id']
        );
    }

    private function sendSticker(string $response, array $update) {
        $this->telegram->sendChatAction($update['message']['chat']['id'], 'choose_sticker');
        $delay = rand(2000000, 5000000);
        if($delay>5000000) {
            $delay = 5000000;
        }
        usleep($delay);

        return $this->telegram->sendSticker(
            $update['message']['chat']['id'],
            $response,
            $update['message']['message_id'],
            'MarkdownV2',
            true
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