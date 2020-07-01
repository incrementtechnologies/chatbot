<?php

use Illuminate\Database\Seeder;

class BusinessTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB:: table('business_types') -> insert(array(
          array("id" => "1","category" => "Entertainment", "sub_category" => "KTV"),
          array("id" => "2","category" => "Entertainment", "sub_category" => "Restaurant"),
          array("id" => "3","category" => "Transportation", "sub_category" => "Shippings Lines"),
          array("id" => "4","category" => "Payment", "sub_category" => "School"),
          array("id" => "5","category" => "Entertainment", "sub_category" => "Cinema")
        ));
    }
}
