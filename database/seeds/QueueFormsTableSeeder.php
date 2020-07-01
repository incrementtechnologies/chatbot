<?php

use Illuminate\Database\Seeder;

class QueueFormsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB:: table('queue_forms') -> insert(array(
          array(
            "id"    => "1",
            "company_id" => "1",
            "availability" => "1",
            "detail" => "KTV Reservation Form",
            "title"  => "Reservation",
            "is_private"  => "0"
          ),
          array(
            "id"    => "2",
            "company_id" => "2",
            "availability" => "1",
            "detail" => "Shippings Ticket from Cebu to Siquijor",
            "title"  => "Ticket",
            "is_private"  => "0"
          ),
          array(
            "id"    => "3",
            "company_id" => "3",
            "availability" => "1",
            "detail" => "Movie Ticket",
            "title"  => "Ticket",
            "is_private"  => "0"
          ),
          array(
            "id"    => "4",
            "company_id" => "4",
            "availability" => "1",
            "detail" => "Restaurant Reservation Form",
            "title"  => "Reservation",
            "is_private"  => "0"
          ),
          array(
            "id"    => "5",
            "company_id" => "5",
            "availability" => "1",
            "detail" => "Pay Tuition Fee Form",
            "title"  => "Payment",
            "is_private"  => "0"
          ),
          array(
            "id"    => "6",
            "company_id" => "6",
            "availability" => "1",
            "detail" => "Survey Form",
            "title"  => "Survey",
            "is_private"  => "0"
          )
        ));
    }
}
