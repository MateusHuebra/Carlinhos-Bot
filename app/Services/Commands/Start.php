<?php

namespace App\Services\Commands;

use App\Models\Chat;
use App\Models\Pattern;

class Start implements Command {

    function handle($update, $telegram) {
        if($update['message']['chat']['type']=='private') {
            return self::addNewChat($update['message']['chat']['id']);
        }

        return Pattern::firstWhere('name', 'welcome carlinhos');
    }

    static function addNewChat($chatId) {
        $chat = new Chat();
        $chat->id = $chatId;
        $chat->nsfw = false;
        $chat->custom_welcome = null;
        $chat->save();
        return Pattern::firstWhere('name', 'welcome carlinhos');
    }

}