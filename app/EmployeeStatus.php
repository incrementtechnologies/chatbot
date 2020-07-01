<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeStatus extends APIModel
{
  protected $table = 'employee_status';
  public function account(){
    return $this->belongsTo('App\Account', 'id');
  }
}
