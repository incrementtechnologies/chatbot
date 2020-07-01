<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountSchedule extends APIModel
{
    
    public function schedule(){
      return $this->belongsTo('App\Schedule', 'id');
    }

    public function account(){
      return $this->belongsTo('App\Account', 'id');
    }
}
