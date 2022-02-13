<?php

namespace App\Services\Updates;

use App\Models\Pattern;

interface Update {

    /**
     * Handle the update sent by Telegram API depending on update type
     * @param array $update request sent by Telegram API
     * @return Pattern pattern which matched update
     */
    function handle(array $update);

}