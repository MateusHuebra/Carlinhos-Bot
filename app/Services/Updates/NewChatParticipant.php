<?php

namespace App\Services\Updates;

use App\Models\Pattern;

class NewChatParticipant implements Update {

    function handle(array $update) {
        if($update['message']['new_chat_member']['id']==env('T_BOT_ID')) {
            return Pattern::firstWhere('name', 'welcome carlinhos');
        }
        return Pattern::firstWhere('name', 'welcome');
    }

}