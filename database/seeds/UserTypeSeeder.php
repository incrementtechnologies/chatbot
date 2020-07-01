<?php

use Illuminate\Database\Seeder;
Use Illuminate\Database\Eloquent\Model;
use App\UserType;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      Model::unguard();

      DB::table('user_types')->delete();

      $userTypes = array(
          ['description' => 'admin'],
          ['description' => 'staff'],
          ['description' => 'registered_customer'],
          ['description' => 'customer']
      );

      // Loop through each user above and create the record for them in the database
      foreach ($userTypes as $userType)
      {
          UserType::create($userType);
      }

      Model::reguard();
    }
}
