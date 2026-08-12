<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'username' => 'admin',
                'password_hash' => Hash::make('admin123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'petugas',
                'password_hash' => Hash::make('petugas123'),
                'role' => 'petugas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
