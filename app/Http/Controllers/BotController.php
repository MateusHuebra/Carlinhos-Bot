<?php

namespace App\Http\Controllers;

use App\Services\Replies;
use App\Services\TelegramAPI;
use App\Services\Updates\Factory;
use Illuminate\Http\Request;
use TelegramBot\Api\BotApi;

class BotController extends Controller
{
    
    /**
     * Listen to the requests sent by Telegram API
     */
    public function listen(Request $request) {

        //log request sent by Telegram API
        file_put_contents('php://stderr', "\n\n\n".json_encode($request->all(), JSON_PRETTY_PRINT));

        //create and handle update sent by Telegram API acording to its type
        $update = (new Factory)->create($request->all());
        $matchedPattern = $update->handle($request->all());
        
        if($matchedPattern) {
            $telegram = new TelegramAPI();
            (new Replies)->handle($matchedPattern->id, $request->all(), $telegram);
        }

    }

}
