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
        
        if(isset($update['message']['text'])) {
            file_put_contents('php://stderr', "\n\n update type: message (text)");
            return new Message('text');
        } else if(isset($update['message']['caption'])) {
            file_put_contents('php://stderr', "\n\n update type: message (caption)");
            return new Message('caption');
        } else if(isset($update['message']['new_chat_participant'])) {
            file_put_contents('php://stderr', "\n\n update type: new chat participant");
            return new NewChatParticipant();
        }
        
        file_put_contents('php://stderr', "\n\n not handlable Update type");
        return null;
    }

}