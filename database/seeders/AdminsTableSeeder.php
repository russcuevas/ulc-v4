<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admins;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admins::create([
            'fullname' => 'Super Admin',
            'email' => 'ulc@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => '09495749301',
            'gender' => 'female',
            'status' => 'verified',
        ]);
    }
}
