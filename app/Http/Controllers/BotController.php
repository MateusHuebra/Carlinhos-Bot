<?php

namespace App\Http\Controllers;

use App\Services\Replies;
use App\Services\TelegramAPI;
use App\Services\Updates\Factory;
use Illuminate\Http\Request;

class BotController extends Controller
{
    
    /**
     * Listen to the requests sent by Telegram API
     */
    public function listen(Request $request) {

        //create and handle update sent by Telegram API acording to its type
        $update = (new Factory)->create($request->all());

        if ($update===null) {
            die();
        }

        $telegram = new TelegramAPI();
        $telegram->sendLog($request->all());

        $matchedPattern = $update->handle($request->all());
    
        if($matchedPattern) {
            (new Replies)->handle($matchedPattern->id, $request->all(), $telegram);
        }

    }

}
