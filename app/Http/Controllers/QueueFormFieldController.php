<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\QueueFormField as DBItem;
class QueueFormFieldController extends APIController
{

    function __construct(){
      $this->model = new DBItem();
    }
}
