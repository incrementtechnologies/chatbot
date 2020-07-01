<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CompaniesTableSeeder::class);
        $this->call(CompanyBranchesTableSeeder::class);
        $this->call(CompanyBranchEmployeesTableSeeder::class);
        $this->call(QueueFormsTableSeeder::class);
        //$this->call(QueueFormFieldsTableSeeder::class);
        //$this->call(UserTypesSeeder::class);
        Model::unguard();



        Model::reguard();

    }
}
