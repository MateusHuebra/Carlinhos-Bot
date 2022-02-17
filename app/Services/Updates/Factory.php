<?php

namespace App\Services\Updates;

class Factory {

    /**
     * Create and return an Update class based on the request sent by Telegram API
     * @param array $update request sent by Telegram API
     * @return Update|Null corresponding update class
     */
    public function create(array $update) {
        file_put_contents('php://stderr', "\n\n\n".json_encode($update, JSON_PRETTY_PRINT));
        
        if(isset($update['message']['entities']) && $update['message']['entities'][0]['type']==='bot_command') {
            $updateType = "command (text)";
            $update = new Command('text', 'entities');
        } else if(isset($update['message']['caption_entities']) && $update['message']['caption_entities'][0]['type']==='bot_command') {
            $updateType = "command (caption)";
            $update = new Command('caption', 'caption_entities');
        } else if(isset($update['message']['text'])) {
            $updateType = "message (text)";
            $update = new Message('text');
        } else if(isset($update['message']['caption'])) {
            $updateType = "message (caption)";
            $update = new Message('caption');
        } else if(isset($update['message']['new_chat_participant'])) {
            $updateType = "new chat participant";
            $update = new NewChatParticipant();
        } else {
            $updateType = "not handlable";
            $update = null;
        }
        
        file_put_contents('php://stderr', "\n\n update type: ".$updateType);
        return $update;
        
    }

}