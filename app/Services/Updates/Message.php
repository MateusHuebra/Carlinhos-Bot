<?php

namespace App\Services\Updates;

class Message implements Update {

    private $textIndex;

    function __construct($textIndex) {
        $this->textIndex = $textIndex;
    }

    function handle(array $update) {
        
    }

}