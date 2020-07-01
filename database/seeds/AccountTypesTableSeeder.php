<?php

use Illuminate\Database\Seeder;

class AccountTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB:: table('account_types') -> insert(array(
          array("id" => "1","title" => "Admin", "description" => "Have access to all modules"), 
          array("id" => "2","title" => "Employee", "description" => "Have access to the ff. modules:"), 
        ));
    }
}
