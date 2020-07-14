<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountInformationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('account_informations');
        Schema::create('account_informations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id');
            $table->unsignedInteger('account_type_id')->default(0);
            $table->string('first_name',100)->nullable();
            $table->string('middle_name',100)->nullable();
            $table->string('last_name',100)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('sex',20)->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->string('telephone_number',20)->nullable();
            $table->string('cellular_number',20)->nullable();
            $table->string('current_address')->nullable();
            $table->string('home_address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Artisan::call('db:seed', array('--class' => 'AccountInformationsTableSeeder'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_informations');
    }
}
