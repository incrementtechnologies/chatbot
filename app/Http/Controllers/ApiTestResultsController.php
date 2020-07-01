<?php

namespace App\Http\Controllers;

use App\ApiTestResults;
use Illuminate\Http\Request;
use DB;

class ApiTestResultsController extends APIController
{
    function __construct(){
        $this->model = new ApiTestResults();
    }
}
  