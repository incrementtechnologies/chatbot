<?php

namespace App\Http\Controllers;
use App\QueueForm as DBItem;
use Illuminate\Http\Request;

class QueueFormController extends APIController
{
    function __construct(){
      $this->model = new DBItem();
      $this->editableForeignTable = array(
        'queue_form_fields'
      );
      $this->defaultValue = array(
        'company_id' => $this->getUserCompanyID(),
        'availability' => 1,
        'is_private' => 0
      );
    }
}
