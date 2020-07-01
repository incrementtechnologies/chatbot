<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends APIModel
{
    
    public function  company_branch(){
      return $this->belongsTo('App\CompanyBranch', 'id');
    }

    public function account_schedules(){
      return $this->hasMany('App\AccountSchedule');
    }
}
