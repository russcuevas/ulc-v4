<?php

namespace Database\Seeders;

use App\Models\Management;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Seeder;

class ManagementTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Management::create([
            'fullname' => 'Management',
            'email' => 'micro@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => null,
            'gender' => null,
        ]);
    }
}
