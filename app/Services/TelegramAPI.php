<?php

namespace App\Services;

use App\Models\Reply;
use App\Services\Updates\Update;
use Exception;
use TelegramBot\Api\BotApi;

class TelegramAPI {
    
    private $telegram;

    public function __construct() {
        $this->telegram = new BotApi(env('TELEGRAM_API_TOKEN'));
    }

    public function send(string $response, Reply $reply, array $update) {
        file_put_contents('php://stderr', "\n\n preparing to send");

        try {
            if ($reply->type==='message') {
                $telegramResponse = $this->sendMessage($response, $update);
            } elseif ($reply->type==='sticker') {
                $telegramResponse = $this->sendSticker($response, $update);
            } elseif ($reply->type==='photo') {
                $telegramResponse = $this->sendPhoto($response, $update);
            }
            file_put_contents('php://stderr', "\n sent");
        } catch (Exception $e) {
            file_put_contents('php://stderr', "\n exception thrown: ".$e->getMessage());
        }
        
    }

    private function sendMessage(string $response, array $update) {
        $length = strlen($response);
        $this->chatActionWithDelay($update['message']['chat']['id'], 'typing', $length);

        return $this->telegram->sendMessage(
            $update['message']['chat']['id'],
            $response,
            'MarkdownV2',
            true,
            $update['message']['message_id']
        );
    }

    private function sendSticker(string $response, array $update) {
        $this->chatActionWithDelay($update['message']['chat']['id'], 'choose_sticker');

        return $this->telegram->sendSticker(
            $update['message']['chat']['id'],
            $response,
            $update['message']['message_id']
        );
    }

    private function sendPhoto(string $response, array $update) {
        $this->chatActionWithDelay($update['message']['chat']['id'], 'upload_photo');

        return $this->telegram->sendPhoto(
            $update['message']['chat']['id'],
            $response,
            null,
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

    public function isMemberAdminOrInPrivate($chatId, $userId) {
        if($chatId==$userId) {
            return true;
        }
        $user = $this->telegram->getChatMember($chatId, $userId);
        file_put_contents('php://stderr', "\n\n getChatMember: ".print_r($user, true));
        if(in_array($user->getStatus(), ['administrator', 'creator'])) {
            return true;
        }
        return false;
    }

    public function sendLog(array $update) {
        file_put_contents('php://stderr', "\n preparing log");
        try {
            if (isset($update['message']['chat']['id']) && $update['message']['chat']['id']!=env('T_ADMIN_ID')) {
                file_put_contents('php://stderr', "\n checking needed media");
                if (isset($update['message']['sticker'])) {
                    $this->telegram->sendSticker(env('T_ADMIN_ID'), $update['message']['sticker']['file_id']);
                } else if (isset($update['message']['photo'])) {
                    $this->telegram->sendPhoto(env('T_ADMIN_ID'), $update['message']['photo'][0]['file_id'], $update['message']['caption']);
                } else if (isset($update['message']['video'])) {
                    $this->telegram->sendVideo(env('T_ADMIN_ID'), $update['message']['video']['file_id'], null, $update['message']['caption']);
                } else if (isset($update['message']['animation'])) {
                    $this->telegram->sendAnimation(env('T_ADMIN_ID'), $update['message']['animation']['file_id'], $update['message']['caption']);
                }
                file_put_contents('php://stderr', " - needed media sent");

                $response = '';
                if (isset($update['message']['reply_to_message']['text'])) {
                    $response.= '_'.$update['message']['reply_to_message']['text'].'_'.PHP_EOL;
                }
                if (isset($update['message']['reply_to_message']['from']['first_name'])) {
                    $response.= '_por ['.$update['message']['reply_to_message']['from']['first_name'].' '.($update['message']['reply_to_message']['from']['last_name']??'').'](tg://user?id='.$update['message']['reply_to_message']['from']['id'].')_';
                    $response.= PHP_EOL.'user id: '.($update['message']['reply_to_message']['from']['id']??'nulo').PHP_EOL.PHP_EOL;
                }
                if (isset($update['message']['sticker']['file_id'])||isset($update['message']['animation']['file_id'])||isset($update['message']['photo'][0]['file_id'])) {
                    $response.= '`'.(($update['message']['sticker']['file_id']??$update['message']['animation']['file_id'])??$update['message']['photo'][0]['file_id']).'`'.PHP_EOL;
                }
                if (isset($update['message']['text'])) {
                    $response.= '*'.($update['message']['text']??'').'*'.PHP_EOL;
                }
                if (isset($update['message']['from']['first_name'])) {
                    $response.= '*por ['.$update['message']['from']['first_name'].' '.($update['message']['from']['last_name']??'').'](tg://user?id='.$update['message']['from']['id'].')*';
                    $response.= PHP_EOL.'user id: '.($update['message']['from']['id']??'nulo');
                    $response.= PHP_EOL.'msg id: '.($update['message']['message_id']??'nulo');
                }
                $response.= PHP_EOL.PHP_EOL.'em '.($update['message']['chat']['title']??'privado').PHP_EOL.'link: @'.($update['message']['chat']['username']??' sem link');
                $response.= PHP_EOL.'chat id: \\'.($update['message']['chat']['id']??'nulo');
        
                file_put_contents('php://stderr', "\n sending log to DM");
                $this->telegram->sendMessage(env('T_ADMIN_ID'), $response, 'MarkdownV2');
                file_put_contents('php://stderr', " - log sent");
            }
        } catch (Exception $e) {
            file_put_contents('php://stderr', "\n exception thrown: ".$e->getMessage());
        }

    }

    static function parseForMarkdownV2(string $text) {
        return str_replace(
            ['\\', '_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            ['\\\\', '\_', '\*', '\[', '\]', '\(', '\)', '\~', '\`', '\>', '\#', '\+', '\-', '\=', '\|', '\{', '\}', '\.', '\!'],
            $text
        );
    }

    private function chatActionWithDelay($chatId, string $action, $strlen = null) {
        if(in_array($chatId, [-1001327613590, env('T_ADMIN_ID')])) {
            return;
        }

        $this->telegram->sendChatAction($chatId, $action);
        if($strlen) {
            $delay = $strlen * 200000;
            if($delay>5000000) {
                $delay = 5000000;
            }
        } else {
            $delay = rand(1000000, 5000000);
        }
        usleep($delay);
    }

}