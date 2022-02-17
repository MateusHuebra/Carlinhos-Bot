<?php

namespace App\Services\Commands;

use App\Models\Chat;
use App\Models\Pattern;

class Settings implements Command {

    function handle($chatId) {
        //$settings = Chat::firstWhere('id', $chatId);
        return Pattern::firstWhere('name', 'settings');
    }

}