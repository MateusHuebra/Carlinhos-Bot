<?php

namespace App\Services\Updates;

class Factory {

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