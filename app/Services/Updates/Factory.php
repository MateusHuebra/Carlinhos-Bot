<?php

namespace App\Services\Updates;

class Factory {

    /**
     * Create and return an Update class based on the request sent by Telegram API
     * @param array $update request sent by Telegram API
     * @return Update corresponding update class
     */
    public function create(array $update) : Update {
        if(isset($update['message']['text'])) {
            return new Message('text');
        } else if(isset($update['message']['caption'])) {
            return new Message('caption');
        } else if(isset($update['message']['new_chat_participant'])) {
            return new NewChatParticipant();
        }
    }

}