<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminPassword = Hash::make('admin');
        $employeePassword = Hash::make('employee');
        DB:: table('accounts') -> insert(array(
          array("id" => "1","username" => "Admin", "email" => "Admin@gocentralph.com", "password" => $adminPassword), 
          array("id" => "2","username" => "Employee", "email" => "Employee@gocentralph.com", "password" => $employeePassword),
        ));
    }
}
