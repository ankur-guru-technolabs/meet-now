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
            'email' => 'admin@gmail.com',
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
            'password' => bcrypt('123456'),
            'user_type' =>'admin',
        ]);
    }
}
