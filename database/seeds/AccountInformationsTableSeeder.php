<?php

use Illuminate\Database\Seeder;

class AccountInformationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB:: table('account_informations') -> insert(array(
          array(
            "id"          => "1",
            "account_id"  => "1",
            "account_type_id" => "1",
            "first_name"  => "Kennette",
            "middle_name" => "Jumao-as",
            "last_name"   => "Canales",
            "birth_date"  => date("2017-07-19"),
            "sex"         => "Male",
            "marital_status"    => "Single",
            "telephone_number"  => "09423873602",
            "cellular_number"   => "09423873602",
            "current_address"   => "Cebu City",
            "home_address"      => "Cebu City",
            "created_at"        => date("2017-07-10")
          ),
          array(
            "id"          => "2",
            "account_id"  => "2",
            "account_type_id" => "1",
            "first_name"  => "John",
            "middle_name" => "-",
            "last_name"   => "Plenos",
            "birth_date"  =>  date("2017-07-19"),
            "sex"         => "Male",
            "marital_status"    => "Single",
            "telephone_number"  => "1234",
            "cellular_number"   => "1234",
            "current_address"   => "Cebu City",
            "home_address"      => "Cebu City",
            "created_at"        => date("2017-07-10")
          ),

        ));
    }
}
