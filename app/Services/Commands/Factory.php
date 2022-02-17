<?php

namespace App\Services\Commands;

class Factory {


    public function create(array $commands) {
        
        if($this->checkCommand('/settings', $commands[1])) {
            $command = new Settings();
        } else if ($this->checkCommand('/nsfw', $commands[1])) {
            $command = new NSFW($commands[3]??null);
        } else if ($this->checkCommand('/start', $commands[1])) {
            $command = new Start($commands[3]??null);
        } else {
            $command = null;
        }
        
        return $command;
        
    }

    private function checkCommand(string $expectedCommand, string $command) {
        if($expectedCommand == $command || $expectedCommand.'@carlosbot' == $command) {
            return true;
        }
        return false;
    }

}