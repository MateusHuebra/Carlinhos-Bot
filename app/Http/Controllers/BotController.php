<?php

namespace App\Http\Controllers;

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
        //file_put_contents('php://stderr', "\n\n\n".json_encode($request->all(), JSON_PRETTY_PRINT));

        //create and handle update sent by Telegram API acording to its type
        $update = (new Factory)->create($request->all());
        $matchedPattern = $update->handle($request->all());

        if($matchedPattern) {
            $reply = 'regex found: '.$matchedPattern->regex;
        } else {
            $reply = 'nothing found';
        }

        $bot = new BotApi(env('TELEGRAM_API_TOKEN'));
        $bot->sendMessage($request->input('message')['chat']['id'], $reply, null, false, $request->input('message')['message_id']);

    }

}
