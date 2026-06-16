<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Secretary;

class SecretaryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Secretary::create([
            'fullname' => 'Erica Tinio',
            'email' => 'ericatinio@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => '09123456781',
            'gender' => 'female',
            'status' => 'verified',
        ]);

        Secretary::create([
            'fullname' => 'Princess Divine Gambet',
            'email' => 'princessdivine@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => '09123456782',
            'gender' => 'female',
            'status' => 'verified',
        ]);

        Secretary::create([
            'fullname' => 'Faljemarick Reynante',
            'email' => 'faljemarickreynante@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => '09123456783',
            'gender' => 'female',
            'status' => 'verified',
        ]);

        Secretary::create([
            'fullname' => 'Hydie Cadiz',
            'email' => 'hydiecadiz@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => '09123456783',
            'gender' => 'female',
            'status' => 'verified',
        ]);
    }
}
