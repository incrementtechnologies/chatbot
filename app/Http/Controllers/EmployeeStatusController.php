<?php

namespace App\Http\Controllers;

use App\EmployeeStatus;
use Illuminate\Http\Request;

class EmployeeStatusController extends APIController
{
    function __construct(){
        $this->model = new EmployeeStatus();
    }
}
