<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountPosition extends APIModel
{
  protected $table = 'account_positions';
  public function position(){
    return $this->belongsTo('App\Position', 'id');
  }

  public function account(){
    return $this->belongsTo('App\Account', 'id');
  }
  
}
