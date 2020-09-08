<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BotConfiguration extends Model
{
    protected $table = "bot_configurations";
    protected $fillable = ['account_id', 'payload', 'payload_value', 'page', 'token'];
}
