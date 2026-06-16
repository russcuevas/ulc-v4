<?php

namespace App\Http\Controllers\collector;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectorDashboardController extends Controller
{
    public function CollectorDashboardPage()
    {
        $user = Session::get('user');
        $collectorId = $user->id;

        // Get assigned areas
        $areas = DB::table('areas')
            ->where('collector_id', $collectorId)
            ->get();

        $locationName = $areas->first()?->location_name ?? 'No assigned area';

        return view('collector.dashboard.index', compact('areas', 'locationName'));
    }
}
