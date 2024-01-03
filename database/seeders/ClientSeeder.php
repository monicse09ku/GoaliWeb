<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Client::create([
            'user_id' => 3,
            'first_name' => 'Super Admin',
            'last_name' => 'Super Admin',
            'email' => 'super_admin@gmail.com',
            'about_me' => '',
            'hobbies' => '',
            'languages' => '',
            'core_skills'  => 'HTML, PHP',
            'status'  => 'active',
            'created_at'  => date('Y-m-d h:i:s')
        ]);
    }
}
