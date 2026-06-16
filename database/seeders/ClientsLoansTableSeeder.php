<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientsLoansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::now()->format('d-m-y');

        DB::table('clients_loans')->insert([
            [
                'client_id' => 1,
                'pn_number' => 'MA12026-' . $today . '-01',
                'release_number' => 'MA12026-' . $today . '-01',
                'loan_from' => now(),
                'loan_to' => now()->addDays(30),
                'loan_amount' => 10000,
                'balance' => 10000,
                'daily' => 350,
                'principal' => 10000,
                'loan_status' => 'new',
                'loan_terms' => '100',
                'status' => 'unpaid',
                'created_by' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'client_id' => 2,
                'pn_number' => 'VA12026-' . $today . '-01',
                'release_number' => 'VA12026-' . $today . '-01',
                'loan_from' => now(),
                'loan_to' => now()->addDays(30),
                'loan_amount' => 20000,
                'balance' => 20000,
                'daily' => 700,
                'principal' => 20000,
                'loan_status' => 'new',
                'loan_terms' => '100',
                'status' => 'unpaid',
                'created_by' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
