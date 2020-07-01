<?php

use Illuminate\Database\Seeder;

class CompanyBranchEmployeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB:: table('company_branch_employees') -> insert(array(
          array(
            "id"    => "1",
            "company_branch_id" => "1",
            "account_id" => '1',
            'identification_number' => '0000-0001'
          ),
          array(
            "id"    => "2",
            "company_branch_id" => "2",
            "account_id" => '1',
            'identification_number' => '0000-0001'
          ),
          array(
            "id"    => "3",
            "company_branch_id" => "3",
            "account_id" => '1',
            'identification_number' => '0000-0001'
          ),
        ));
    }
}
