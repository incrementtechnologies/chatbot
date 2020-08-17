<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixUserFacebookId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('facebook_users', function(Blueprint $table){
        $table->renameColumn('facebook_id', 'account_number');
      });
      Schema::table('queue_cards', function(Blueprint $table){
        $table->renameColumn('user_id', 'facebook_user_id');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
