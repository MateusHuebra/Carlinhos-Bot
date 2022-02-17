<?php

namespace App\Services\Updates;

use App\Models\Chat;
use App\Models\Pattern;
use App\Services\Commands\Start;
use App\Services\TelegramAPI;

class NewChatParticipant implements Update {

    function handle(array $update, TelegramAPI $telegram) {
        if($update['message']['new_chat_member']['id']==env('T_BOT_ID')) {
            return Start::addNewChat($update['message']['chat']['id']);
        }
        return Pattern::firstWhere('name', 'welcome');
    }

}