<?php

namespace App\Services\Updates;

interface Update {

    function handle(array $update);

}