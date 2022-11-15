<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            "name"=> "admin",
            "role"=> "admin",
            "email"=> "admin@kargo.com",
            "password"=> "12345678",
            "email_verified_at"=> now(),
        ]);
    }
}
