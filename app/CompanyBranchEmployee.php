<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyBranchEmployee extends APIModel
{
  protected $fillable = ['identification_number', 'account_id', 'company_branch_id'];
  public function company_branch(){
    return $this->belongsTo('App\CompanyBranch', 'company_branch_id');
  }
  public function account(){
    return $this->belongsTo('App\account', 'id');
  }
}