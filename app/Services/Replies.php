<?php

namespace App\Services;

use App\Models\Reply;

class Replies {

    public function handle(int $patternId, array $update, TelegramAPI $telegram) {
        $reply = $this->randomReply($patternId);
        $response = $this->replaceVariables($reply->reply, $update, $telegram);
        $telegram->send($response, $reply, $update);
    }

    private function randomReply(int $patternId) : Reply {
        $replyQueryBuilder = Reply::where('pattern_id', $patternId);
        $repliesCount = $replyQueryBuilder->count();
        $randomIndex = rand(0, $repliesCount-1);
        return $replyQueryBuilder->skip($randomIndex)->first();
    }

    private function replaceVariables(string $replyText, array $update, TelegramAPI $telegram) : string {
        if(isset($update['message'])) {
            
            if(strpos($replyText, '{chat_members_count}')!==false) {
                $replyText = str_replace('{chat_members_count}', $telegram->get('chatMembersCount', $update), $replyText);
            }

            if(strpos($replyText, '{from_name}')!==false) {
                $names[] = $update['message']['from']['first_name'];
                if(isset($update['message']['from']['last_name'])) {
                    $names[] = $update['message']['from']['first_name'].' '.$update['message']['from']['last_name'];
                }
                $name = $names[rand(0, count($names)-1)];
                $replyText = str_replace('{from_name}', $name, $replyText);
            }
            
            if(strpos($replyText, '{reply_name}')!==false && isset($update['message']['reply_to_message'])) {
                $names[] = $update['message']['reply_to_message']['from']['first_name'];
                if(isset($update['message']['reply_to_message']['from']['last_name'])) {
                    $names[] = $update['message']['reply_to_message']['from']['first_name'].' '.$update['message']['reply_to_message']['from']['last_name'];
                }
                $name = $names[rand(0, count($names)-1)];
                $replyText = str_replace('{reply_name}', $name, $replyText);
            }

        }
        return $replyText;
    }

}