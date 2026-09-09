<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LingkunganUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'lingkungan'],
            [
                'password_hash' => Hash::make('lingkungan123'),
                'role'          => 'lingkungan',
                'photo'         => null,
            ]
        );
    }
}