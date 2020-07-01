<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReportedUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('reported_users', function(Blueprint $table){
        $table->increments('id');
        $table->integer('company_id');
        $table->integer('facebook_user_id');
        $table->string('reason');
        $table->integer('status')->comment('1 - pending, 2 - processing, 3 - resolved');
        $table->timestamps();
        $table->softDeletes();
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
