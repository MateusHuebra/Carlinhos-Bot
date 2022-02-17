<?php

namespace App\Services;

use App\Models\Reply;

class Replies {

    public function handle(int $patternId, array $update, TelegramAPI $telegram) {
        $reply = $this->randomReply($patternId);
        if($reply===null) {
            die();
        }
        $response = $reply->reply;
        if($reply->type==='message') {
            $response = $this->replaceVariables($response, $update, $telegram);
        }
        $telegram->send($response, $reply, $update);
    }

    private function randomReply(int $patternId) {
        $replyQueryBuilder = Reply::where('pattern_id', $patternId);
        $repliesCount = $replyQueryBuilder->count();
        if($repliesCount===0) {
            file_put_contents('php://stderr', "\n\n no replies linked to pattern");
            return null;
        }
        $randomIndex = rand(0, $repliesCount-1);
        return $replyQueryBuilder->skip($randomIndex)->first();
    }

    private function replaceVariables(string $replyText, array $update, TelegramAPI $telegram) : string {
            
        if(strpos($replyText, '{chat_members_count}')!==false) {
            $replyText = str_replace('{chat_members_count}', $telegram->get('chatMembersCount', $update), $replyText);
        }

        if(isset($update['message'])) {

            if(strpos($replyText, '{new_chat_member}')!==false) {
                $name = $this->getName($update['message']['new_chat_member']);
                $replyText = str_replace('{new_chat_member}', $name, $replyText);
            }

            if(strpos($replyText, '{from_name}')!==false) {
                $name = $this->getName($update['message']['from']);
                $replyText = str_replace('{from_name}', $name, $replyText);
            }
            
            if(strpos($replyText, '{reply_name}')!==false && isset($update['message']['reply_to_message'])) {
                $name = $this->getName($update['message']['reply_to_message']['from']);
                $replyText = str_replace('{reply_name}', $name, $replyText);
            }

        }

        $replyText = TelegramAPI::parseForMarkdownV2($replyText);

        return $replyText;
    }

    public function getName($names, bool $fullName = false) {
        $name[] = $names['first_name'];
        if(isset($names['last_name'])) {
            $name[] = $names['first_name'].' '.$names['last_name'];
            if($fullName) {
                return $name[1];
            }
        }
        return $name[rand(0, count($name)-1)];
    }

}