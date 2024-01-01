<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'super_admin@gmail.com',
            'username' => 'super_admin',
            'password' => Hash::make('123456'),
            'role'  => 1,
            'created_at'  => date('Y-m-d h:i:s')
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'username' => 'admin',
            'password' => Hash::make('123456'),
            'role'  => 2,
            'created_at'  => date('Y-m-d h:i:s')
        ]);

        User::create([
            'name' => 'Client',
            'email' => 'client@gmail.com',
            'username' => 'client',
            'password' => Hash::make('123456'),
            'role'  => 3,
            'created_at'  => date('Y-m-d h:i:s')
        ]);
    }
}
