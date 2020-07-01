<?php

use Illuminate\Database\Seeder;

class QueueFormFieldsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB:: table('queue_form_fields') -> insert(array(

          /*
            K1
          */
          array(
            "id"    => "1", "queue_form_id" => "1", "is_admin_only" => '0', "sequence" => "1","description"  => "What is your Complete Name?", "type" => "text"
          ),
          array(
            "id"    => "2", "queue_form_id" => "1", "is_admin_only" => '0', "sequence" => "2","description"  => "What is your Contact Number?", "type" => "number"
          ),
          array(
            "id"    => "3", "queue_form_id" => "1", "is_admin_only" => '0', "sequence" => "3","description"  => "How many are you?", "type" => "number"
          ),
          array(
            "id"    => "4", "queue_form_id" => "1", "is_admin_only" => '0', "sequence" => "4","description"  => "How many hours you want to reserve?", "type" => "number"
          ),

          /*
            Roble
          */
          array(
            "id"    => "5", "queue_form_id" => "2", "is_admin_only" => '0', "sequence" => "1","description"  => "What is your Complete Name?", "type" => "text"
          ),
          array(
            "id"    => "6", "queue_form_id" => "2", "is_admin_only" => '0', "sequence" => "2","description"  => "What is your Contact Number?", "type" => "number"
          ),
          array(
            "id"    => "7", "queue_form_id" => "2", "is_admin_only" => '0', "sequence" => "3","description"  => "What is your Home Address?", "type" => "text"
          ),

          /*
            Cinema
          */
          array(
            "id"    => "8", "queue_form_id" => "3", "is_admin_only" => '0', "sequence" => "1","description"  => "What is your Complete Name?", "type" => "text"
          ),
          array(
            "id"    => "9", "queue_form_id" => "3", "is_admin_only" => '0', "sequence" => "2","description"  => "What is your Contact Number?", "type" => "number"
          ),
          array(
            "id"    => "10", "queue_form_id" => "3", "is_admin_only" => '0', "sequence" => "3","description"  => "What is your Email Address?", "type" => "email"
          ),

           /*
            Pino
          */
          array(
            "id"    => "11", "queue_form_id" => "4", "is_admin_only" => '0', "sequence" => "1","description"  => "What is your Complete Name?", "type" => "text"
          ),
          array(
            "id"    => "12", "queue_form_id" => "4", "is_admin_only" => '0', "sequence" => "2","description"  => "What is your Contact Number?", "type" => "number"
          ),
          array(
            "id"    => "13", "queue_form_id" => "4", "is_admin_only" => '0', "sequence" => "3","description"  => "How many are you?", "type" => "text"
          ),
          array(
            "id"    => "14", "queue_form_id" => "4", "is_admin_only" => '0', "sequence" => "4","description"  => "When will eat? Lunch or Dinner?", "type" => "text"
          ),


            /*
            Pino
          */
          array(
            "id"    => "15", "queue_form_id" => "5", "is_admin_only" => '0', "sequence" => "1","description"  => "What is your Complete Name?", "type" => "text"
          ),
          array(
            "id"    => "16", "queue_form_id" => "5", "is_admin_only" => '0', "sequence" => "2","description"  => "What is your ID Number?", "type" => "text"
          ),
          array(
            "id"    => "17", "queue_form_id" => "5", "is_admin_only" => '0', "sequence" => "3","description"  => "How much do you want to pay?", "type" => "number"
          ),
          array(
            "id"    => "18", "queue_form_id" => "5", "is_admin_only" => '0', "sequence" => "4","description"  => "What is the purpose of payment?", "type" => "text"
          ),

          /*
            iLinya Survey Form
          */
          array(
            "id"    => "19", "queue_form_id" => "6", "is_admin_only" => '1', "sequence" => "1","description"  => "Are the statements or instructions clear and easily understood?[5 Highest - 1 Lowest]", "type" => "number"
          ),
          array(
            "id"    => "20", "queue_form_id" => "6", "is_admin_only" => '1', "sequence" => "2","description"  => "Are the functionalities easy to navigate?[5 Highest - 1 Lowest]", "type" => "number"
          ),
          array(
            "id"    => "21", "queue_form_id" => "6", "is_admin_only" => '1', "sequence" => "3","description"  => "What do you like most about iLinya?", "type" => "text"
          ),
          array(
            "id"    => "22", "queue_form_id" => "6", "is_admin_only" => '1', "sequence" => "4","description"  => "What are the issues you encountered by using iLinya?", "type" => "text"
          ),
          array(
            "id"    => "23", "queue_form_id" => "6", "is_admin_only" => '1', "sequence" => "5","description"  => "What are the areas need to improve about iLinya?", "type" => "text"
          ),
          array(
            "id"    => "24", "queue_form_id" => "6", "is_admin_only" => '1', "sequence" => "6","description"  => "Are you excited to use Ilinya soon?[5 Highest - 1 Lowest]", "type" => "number"
          ),
        ));
    }
}
