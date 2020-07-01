<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyBranchEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('company_branch_employees');
        Schema::create('company_branch_employees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_branch_id');
            $table->unsignedInteger('account_id');
            $table->string('identification_number');
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
        Schema::dropIfExists('company_branch_employees');
    }
}
