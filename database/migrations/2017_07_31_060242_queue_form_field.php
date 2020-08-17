<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QueueFormField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('queue_form_fields', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('queue_form_id');
        $table->integer('sequence');
        $table->string('description');
        $table->integer('type');
        $table->string('setting')->nullable();
        $table->boolean('main_field');
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
