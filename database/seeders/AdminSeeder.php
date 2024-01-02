<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $admin = User::create([
            'name' => 'Admin user',
            'email' => 'james@meetnow.com',
            'phone_no' => '+911234567890',
            'location' => 'Raiya Road Rajkot',
            'latitude' => '22.298340',
            'longitude' => '70.786873',
            'birth_date' => '1999-05-15',
            'age' => 24,
            'gender' => 1,
            'interested_gender'=> 1,
            'email_verified'=> 1,
            'phone_verified'=> 1,
            'otp_verified'=> 1,
            'hobbies'=> 2,
            'body_type'=> 2,
            'education'=> 2,
            'exercise'=> 2,
            'religion'=> 2,
            'aboutb'=> "Hy i am admin",
            'distance_in'=> 0,
            'password' => bcrypt('James1234!@#'),
            'user_type' =>'admin',
        ]);
    }
}
