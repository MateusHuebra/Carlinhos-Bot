<?php

namespace App\Services\Updates;

use App\Models\Chat;
use App\Models\Pattern;

class NewChatParticipant implements Update {

    function handle(array $update) {
        if($update['message']['new_chat_member']['id']==env('T_BOT_ID')) {
            $chat = new Chat();
            $chat->id = $update['message']['chat']['id'];
            $chat->custom_welcome = null;
            $chat->save();
            return Pattern::firstWhere('name', 'welcome carlinhos');
        }
        return Pattern::firstWhere('name', 'welcome');
    }

}