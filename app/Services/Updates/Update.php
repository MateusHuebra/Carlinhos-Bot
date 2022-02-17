<?php

namespace App\Services\Updates;

use App\Models\Pattern;
use App\Services\TelegramAPI;

interface Update {

    /**
     * Handle the update sent by Telegram API depending on update type
     * @param array $update request sent by Telegram API
     * @return Pattern pattern which matched update
     */
    function handle(array $update, TelegramAPI $telegram);

}