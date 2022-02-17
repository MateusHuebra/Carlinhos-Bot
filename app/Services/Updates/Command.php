<?php

namespace App\Services\Updates;

use App\Models\Pattern;
use App\Services\Commands\Factory;

class Command implements Update {

    private $textIndex;
    private $entitiesIndex;

    function __construct($textIndex, $entitiesIndex) {
        $this->textIndex = $textIndex;
        $this->entitiesIndex = $entitiesIndex;
    }

    function handle(array $update) {
        preg_match('/^(\/\w+(@[\w]+)?) ?([\w\W]+)?$/i', $update['message'][$this->textIndex], $commands);
        file_put_contents('php://stderr', "\n\n commands: ".print_r($commands, true));
        if(is_array($commands)) {
            $command = (new Factory)->create($commands);
            if($command!==null) {
                return $command->handle($update['message']['chat']['id']);
            }
        }
        return null;
    }

}