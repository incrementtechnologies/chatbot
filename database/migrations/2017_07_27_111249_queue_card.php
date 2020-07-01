<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QueueCard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('queue_cards', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('company_id');
        $table->integer('queue_form_id');
        $table->integer('user_id');
        $table->integer('number');
        $table->integer('status')->comment('1 - onqueue, 2 - serving, 3 - finished');
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
