<?php

namespace App\Services\Updates;

interface Update {

    /**
     * Handle the update sent by Telegram API depending on update type
     * @param array $update request sent by Telegram API
     */
    function handle(array $update);

}