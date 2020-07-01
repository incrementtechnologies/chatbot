<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountProfilePicture extends APIModel
{
    use SoftDeletes;

    protected $table = "account_profile_pictures";

    public function account(){
      return $this->belongsTo('App\Account', 'account_id');
    }
}
