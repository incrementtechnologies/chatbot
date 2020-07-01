<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends APIModel
{
    protected $hidden = array('password');
    protected $fillable = ['email', 'username', 'password','company_id'];

    public function account_information(){
      return $this->hasOne('App\AccountInformation');
    }

    public function account_profile_picture(){
      return $this->hasOne('App\AccountProfilePicture');
    }

    public function account_position(){
      return $this->hasOne('App\AccountPosition');
    }
    public function company_branch_employee(){
      return $this->hasOne('App\CompanyBranchEmployee')->with("company_branch");
    }
}