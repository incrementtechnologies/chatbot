<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BotConfiguration as Bot;

class BotConfigurationController extends APIController
{
    function __construct(){
        $this->model = new Bot();
    }
}
