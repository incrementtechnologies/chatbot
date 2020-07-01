<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBotStatusTracker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('bot_status_tracker');
        Schema::create('bot_status_tracker', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('facebook_id');
            $table->unsignedInteger('status');
            $table->unsignedInteger('stage')->nullable();
            $table->unsignedInteger("business_type_id")->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('form_id')->nullable();
            $table->unsignedInteger('form_sequence')->nullable();
            $table->unsignedInteger("search_option")->nullable();
            $table->boolean("reply")->nullable();
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
        
    }
}
