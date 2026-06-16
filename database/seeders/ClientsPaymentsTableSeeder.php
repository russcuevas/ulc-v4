<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClientsPayments;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClientsPaymentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example: create 10 dummy payment records
        for ($i = 1; $i <= 10; $i++) {
            ClientsPayments::create([
                'reference_number' => 'REF-' . strtoupper(Str::random(8)),
                'collected_by'     => 'Collector ' . $i,
                'due_date'         => Carbon::now()->addDays($i),

                'client_id'        => rand(1, 2),
                'client_loans_id'  => rand(1, 2),
                'client_area'      => rand(1, 2),

                'daily'            => rand(100, 500),
                'old_balance'      => rand(1000, 5000),
                'collection'       => rand(100, 500),

                'type'             => 'gcash',
                'is_lapsed'        => rand(0, 1),
                'is_collected'     => rand(0, 1),

                'created_by'       => 'system',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}