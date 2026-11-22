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
                'name'              => 'Admin Utama',
                'email'             => 'admin@gmail.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'remember_token'    => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'Manager Gudang',
                'email'             => 'manager@gmail.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'remember_token'    => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'name'              => 'Staff Penjualan',
                'email'             => 'staff@gmail.com',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'remember_token'    => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}
