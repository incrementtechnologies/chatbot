<?php

namespace App\Http\Controllers;

use App\AccountSchedule;
use Illuminate\Http\Request;

class AccountScheduleController extends APIController
{
    function __construct(){
        $this->model = new AccountSchedule();
        $this->foreignTable = array(
          'schedule'
        );
    }
}
