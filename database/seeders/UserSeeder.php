<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@bigbag.vn',
                'phone' => '0987654321',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 1,
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Customer One',
                'email' => 'customer1@example.com',
                'phone' => '0912345678',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 1,
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Customer Two',
                'email' => 'customer2@example.com',
                'phone' => '0923456789',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 1,
                'avatar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']], // điều kiện tìm
                $user                       // dữ liệu insert hoặc update
            );
        }
    }
}