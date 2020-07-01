<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends APIModel
{
		protected $table = 'accounts';
    protected $hidden = array('password');

    public function user_type()
    {
        return $this->belongsTo('App\UserType');
    }
    public function user_informations()
    {
        return $this->hasMany('App\UserInformation');
    }
}
