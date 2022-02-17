<?php

namespace App\Services\Commands;

use App\Models\Chat;
use App\Models\Pattern;

class NSFW implements Command {

    private $parameter;

    function __construct($parameter) {
        $this->parameter = $parameter;
    }

    function handle($update, $telegram) {
        if(!$telegram->isMemberAdmin($update['message']['chat']['id'], $update['message']['from']['id'])) {
            return Pattern::firstWhere('name', 'need admin');
        }
        if($this->parameter!='on' && $this->parameter!='off') {
            return Pattern::firstWhere('name', 'wrong expression');
        }
        $chat = Chat::firstWhere('id', $update['message']['chat']['id']);
        $chat->nsfw = ($this->parameter=='on')?true:false;
        $chat->save();
        if($this->parameter=='on') {
            return Pattern::firstWhere('name', 'nsfw on'); 
        } else {
            return Pattern::firstWhere('name', 'nsfw off'); 
        }
        
    }

}