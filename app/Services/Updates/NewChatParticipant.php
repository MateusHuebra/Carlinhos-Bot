<?php

namespace App\Services\Updates;

use App\Models\Pattern;

class NewChatParticipant implements Update {

    function handle(array $update) : Pattern {
        return Pattern::firstWhere('name', 'welcome');
    }

}