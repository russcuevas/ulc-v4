<?php

namespace Database\Seeders;

use App\Models\Areas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AreasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // MANILA AREA
        Areas::create([
            'secretary_id' => 1,
            'collector_id' => 1,
            'location_name' => 'Manila Area',
            'areas_name' => 'MA1'
        ]);

        Areas::create([
            'secretary_id' => 1,
            'collector_id' => 2,
            'location_name' => 'Manila Area',
            'areas_name' => 'MA2'
        ]);

        Areas::create([
            'secretary_id' => 1,
            'collector_id' => 3,
            'location_name' => 'Manila Area',
            'areas_name' => 'MA3'
        ]);
        Areas::create([
            'secretary_id' => 1,
            'collector_id' => 4,
            'location_name' => 'Manila Area',
            'areas_name' => 'MA4'
        ]);
        Areas::create([
            'secretary_id' => 1,
            'collector_id' => 5,
            'location_name' => 'Manila Area',
            'areas_name' => 'MA5'
        ]);
        Areas::create([
            'secretary_id' => 1,
            'collector_id' => 6,
            'location_name' => 'Manila Area',
            'areas_name' => 'MA6'
        ]);
        Areas::create([
            'secretary_id' => 1,
            'collector_id' => 7,
            'location_name' => 'Manila Area',
            'areas_name' => 'MA7'
        ]);
        Areas::create([
            'secretary_id' => 1,
            'collector_id' => 8,
            'location_name' => 'Manila Area',
            'areas_name' => 'MA8'
        ]);
        // END MANILA AREA

        // VALENZUELA AREA
        Areas::create([
            'secretary_id' => 2,
            'collector_id' => 9,
            'location_name' => 'Valenzuela Area',
            'areas_name' => 'CS1'
        ]);

        Areas::create([
            'secretary_id' => 2,
            'collector_id' => 10,
            'location_name' => 'Valenzuela Area',
            'areas_name' => 'CS2'
        ]);

        Areas::create([
            'secretary_id' => 2,
            'collector_id' => 11,
            'location_name' => 'Valenzuela Area',
            'areas_name' => 'VA1'
        ]);
        Areas::create([
            'secretary_id' => 2,
            'collector_id' => 12,
            'location_name' => 'Valenzuela Area',
            'areas_name' => 'VA2'
        ]);
        Areas::create([
            'secretary_id' => 2,
            'collector_id' => 13,
            'location_name' => 'Valenzuela Area',
            'areas_name' => 'VA3'
        ]);
        Areas::create([
            'secretary_id' => 2,
            'collector_id' => 14,
            'location_name' => 'Valenzuela Area',
            'areas_name' => 'VA4'
        ]);
        Areas::create([
            'secretary_id' => 2,
            'collector_id' => 15,
            'location_name' => 'Valenzuela Area',
            'areas_name' => 'VA6'
        ]);
        // END VALENZUELA AREA

        // CALOOCAN AREA
        Areas::create([
            'secretary_id' => 3,
            'collector_id' => 16,
            'location_name' => 'Caloocan Area',
            'areas_name' => 'CA1'
        ]);

        Areas::create([
            'secretary_id' => 3,
            'collector_id' => 17,
            'location_name' => 'Caloocan Area',
            'areas_name' => 'CA2'
        ]);
        Areas::create([
            'secretary_id' => 3,
            'collector_id' => 18,
            'location_name' => 'Caloocan Area',
            'areas_name' => 'CA3'
        ]);
        Areas::create([
            'secretary_id' => 3,
            'collector_id' => 19,
            'location_name' => 'Caloocan Area',
            'areas_name' => 'CA4'
        ]);
        Areas::create([
            'secretary_id' => 3,
            'collector_id' => 20,
            'location_name' => 'Caloocan Area',
            'areas_name' => 'CA5'
        ]);
        Areas::create([
            'secretary_id' => 3,
            'collector_id' => 21,
            'location_name' => 'Caloocan Area',
            'areas_name' => 'CA6'
        ]);
        Areas::create([
            'secretary_id' => 3,
            'collector_id' => 22,
            'location_name' => 'Caloocan Area',
            'areas_name' => 'CA7'
        ]);
        Areas::create([
            'secretary_id' => 3,
            'collector_id' => 23,
            'location_name' => 'Caloocan Area',
            'areas_name' => 'CA8'
        ]);

        // END CALOOCAN AREA

        // FC AREA
        Areas::create([
            'secretary_id' => 4,
            'collector_id' => 24,
            'location_name' => 'Financial Counselor',
            'areas_name' => 'FC1'
        ]);
        Areas::create([
            'secretary_id' => 4,
            'collector_id' => 25,
            'location_name' => 'Financial Counselor',
            'areas_name' => 'FC2'
        ]);
        Areas::create([
            'secretary_id' => 4,
            'collector_id' => 26,
            'location_name' => 'Financial Counselor',
            'areas_name' => 'FC3'
        ]);
        Areas::create([
            'secretary_id' => 4,
            'collector_id' => 27,
            'location_name' => 'Financial Counselor',
            'areas_name' => 'FC4'
        ]);
        Areas::create([
            'secretary_id' => 4,
            'collector_id' => 28,
            'location_name' => 'Financial Counselor',
            'areas_name' => 'FC5'
        ]);
        Areas::create([
            'secretary_id' => 4,
            'collector_id' => 29,
            'location_name' => 'Financial Counselor',
            'areas_name' => 'FC7'
        ]);
        Areas::create([
            'secretary_id' => 4,
            'collector_id' => 30,
            'location_name' => 'Financial Counselor',
            'areas_name' => 'FC8'
        ]);

        Areas::create([
            'secretary_id' => 4,
            'collector_id' => 31,
            'location_name' => 'Financial Counselor',
            'areas_name' => 'FC9'
        ]);
        // END FC AREA
    }
}
