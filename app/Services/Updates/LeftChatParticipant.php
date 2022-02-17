<?php

namespace App\Services\Updates;

use App\Models\Chat;
use App\Models\Pattern;
use App\Services\TelegramAPI;

class LeftChatParticipant implements Update {

    function handle(array $update, TelegramAPI $telegram) {
        if($update['message']['left_chat_member']['id']==env('T_BOT_ID')) {
            //return Pattern::firstWhere('name', 'welcome carlinhos');
            $chat = Chat::firstWhere('id', $update['message']['chat']['id']);
            $chat->delete();
            return null;
        }
        return Pattern::firstWhere('name', 'goodbye');
    }

}