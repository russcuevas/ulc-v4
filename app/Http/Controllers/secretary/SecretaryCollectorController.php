<?php

namespace App\Http\Controllers\secretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SecretaryCollectorController extends Controller
{
    public function SecretaryCollectorPage()
    {
        // ✅ Get currently logged-in secretary
        $secretary = Session::get('user');
        if (!$secretary) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $secretary_id = $secretary->id;

        // ✅ Get areas assigned to this secretary
        $assignedAreas = DB::table('areas')
            ->where('secretary_id', $secretary_id)
            ->pluck('collector_id'); // Get all collector_ids for this secretary

        // ✅ Get collectors assigned to these areas
        $collectors = DB::table('collectors')
            ->whereIn('id', $assignedAreas)
            ->get();

        // ✅ Get collector areas info for display
        $collectorAreas = DB::table('areas')
            ->join('collectors', 'collectors.id', '=', 'areas.collector_id')
            ->where('areas.secretary_id', $secretary_id) // Only show areas for this secretary
            ->select(
                'areas.collector_id',
                'areas.location_name',
                'areas.areas_name',
                'collectors.fullname as collector_name'
            )
            ->get();

        return view('secretary.collector.index', compact('collectors', 'collectorAreas'));
    }

    public function SecretaryUpdateCollector(Request $request, $id)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
        ]);

        DB::table('collectors')->where('id', $id)->update([
            'fullname' => $request->fullname
        ]);

        return redirect()->back()->with('success', 'Collector updated successfully!');
    }
}
