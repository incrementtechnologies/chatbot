<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User as Item;

class UserController extends APIController
{
  function __construct(){
    $this->model = new Item();
    $this->validation = array(
      "username" => "required|unique:users",
      "email" => "required|unique:users",
      "user_informations.business_position_id" => "required",
      "user_informations.first_name" => "required",
      "user_informations.middle_name" => "required",
      "user_informations.last_name" => "required",
      "user_informations.contact_number" => "required"
    );
    $this->editableForeignTable = array(
      "user_informations" => array(
        "no_create_on_update" => true
      )
    );
    $this->foreignTable = array(
      "user_type",
      "user_informations"
    );
  }
}
