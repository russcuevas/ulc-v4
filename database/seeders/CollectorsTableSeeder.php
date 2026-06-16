<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Collector;

class CollectorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $collectors = [

            // MANILA AREA (MA1–MA8)
            ['fullname' => 'Ferdinand Medina', 'email' => 'ferdinandmedina@gmail.com'],
            ['fullname' => 'Erickson Pomaren', 'email' => 'ericksonpomaren@gmail.com'],
            ['fullname' => 'Christian Pinca', 'email' => 'christianpinca@gmail.com'],
            ['fullname' => 'Jason Policarpio', 'email' => 'jasonpolicarpio@gmail.com'],
            ['fullname' => 'Carlo Taperla', 'email' => 'carlotaperla@gmail.com'],
            ['fullname' => 'Boy Cerbito', 'email' => 'boycerbito@gmail.com'],
            ['fullname' => 'Benje Tamayo', 'email' => 'benjetamayo@gmail.com'],
            ['fullname' => 'Patrick Lanuza', 'email' => 'patricklanuza@gmail.com'],

            // VALENZUELA AREA
            ['fullname' => 'James Ojeda', 'email' => 'jamesojeda@gmail.com'],
            ['fullname' => 'Mharlson Tupaz', 'email' => 'mharlsontupaz@gmail.com'],
            ['fullname' => 'Manolito Merabite', 'email' => 'manolitomerabite@gmail.com'],
            ['fullname' => 'Kenneth Dayro', 'email' => 'kennethdayro@gmail.com'],
            ['fullname' => 'Adrian Marcial', 'email' => 'adrianmarcial@gmail.com'],
            ['fullname' => 'Gerson Reyes', 'email' => 'gersonreyes@gmail.com'],
            ['fullname' => 'Rodel Valenzuela', 'email' => 'rodelvalenzuela@gmail.com'],

            // CALOOCAN AREA
            ['fullname' => 'Aeron James Lipan', 'email' => 'aeronjameslipan@gmail.com'],
            ['fullname' => 'Lynnard Harvey Medina', 'email' => 'lynnardharveymedina@gmail.com'],
            ['fullname' => 'Kristoffer Ki', 'email' => 'kristofferki@gmail.com'],
            ['fullname' => 'Jordan Hibo', 'email' => 'jordanhibo@gmail.com'],
            ['fullname' => 'Jesus Napiza', 'email' => 'jesusnapiza@gmail.com'],
            ['fullname' => 'Aldron Paulo Rañeses', 'email' => 'aldronpaulo@gmail.com'],
            ['fullname' => 'Floyd Tumandao', 'email' => 'floydtumandao@gmail.com'],
            ['fullname' => 'Raby De Asis', 'email' => 'rabydeasis@gmail.com'],

            // FC
            ['fullname' => 'Ronel Bravo', 'email' => 'ronelbravo@gmail.com'],
            ['fullname' => 'Rexter Honolario', 'email' => 'rexterhonolario@gmail.com'],
            ['fullname' => 'Norwel Vero', 'email' => 'norwelvero@gmail.com'],
            ['fullname' => 'Kenneth Acquin', 'email' => 'kennethacquin@gmail.com'],
            ['fullname' => 'Bon Jove Flore', 'email' => 'bonjoveflore@gmail.com'],
            ['fullname' => 'Ramoncito Enriquez', 'email' => 'ramoncitoenriquez@gmail.com'],
            ['fullname' => 'John Michael', 'email' => 'johnmichael@gmail.com'],
            ['fullname' => 'Tiffany Changel Mcbride', 'email' => 'tiffanychangelmcbride@gmail.com'],
        ];

        foreach ($collectors as $collector) {
            Collector::create([
                'fullname' => $collector['fullname'],
                'email' => $collector['email'],
                'password' => Hash::make('123456789'),
                'phone' => null,
                'gender' => null,
                'status' => 'verified',
            ]);
        }
    }
}
