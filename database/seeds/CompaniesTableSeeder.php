<?php

use Illuminate\Database\Seeder;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB:: table('companies') -> insert(array(
          array(
            "id"    => "1",
            "business_type_id" => "1",
            "name"  => "K1",
            "address"   => "Crossroads, Cebu City",
            "lat"   => "123.1234556",
            "lng"   => "5.1234556"
          ),
          array(
            "id"    => "2",
            "business_type_id" => "3",
            "name"  => "Roble",
            "address"   => "Cebu City",
            "lat"   => "123.1234556",
            "lng"   => "5.1234556"
          ),
          array(
            "id"    => "3",
            "business_type_id" => "5",
            "name"  => "SM Cinema",
            "address"   => "Cebu City",
            "lat"   => "123.1234556",
            "lng"   => "5.1234556"
          ),
          array(
            "id"    => "4",
            "business_type_id" => "2",
            "name"  => "Pino Restaurant",
            "address"   => "Cebu City",
            "lat"   => "123.1234556",
            "lng"   => "5.1234556"
          ),
          array(
            "id"    => "5",
            "business_type_id" => "4",
            "name"  => "USC - TC Teller",
            "address"   => "Cebu City",
            "lat"   => "123.1234556",
            "lng"   => "5.1234556"
          ),
          array(
            "id"    => "6",
            "business_type_id" => "0",
            "name"  => "iLinya Technologies",
            "address"   => "Cebu City",
            "lat"   => "123.1234556",
            "lng"   => "5.1234556"
          )
        ));
    }
}
