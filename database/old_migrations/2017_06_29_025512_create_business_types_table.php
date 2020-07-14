<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('business_types');
        Schema::create('business_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category',100);
            $table->string('sub_category',100);
            $table->timestamps();
            $table->softDeletes();
        });
        Artisan::call('db:seed', array('--class' => 'BusinessTypeTableSeeder'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_types');
    }
}
