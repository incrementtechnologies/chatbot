<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInformation extends APIModel
{
    protected $fillable = ['id', 'business_position_id', 'first_name', 'middle_name', 'last_name', 'contact_number'];
}
