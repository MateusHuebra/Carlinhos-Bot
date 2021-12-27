<?php

namespace App\Http\Controllers;

use App\Services\Updates\Factory;
use Illuminate\Http\Request;

class BotController extends Controller
{
    
    public function listen(Request $request) {

        file_put_contents('php://stderr', json_encode($request->all()));
        $update = (new Factory)->create($request->all());
        $update->handle($request->all());

    }

}
