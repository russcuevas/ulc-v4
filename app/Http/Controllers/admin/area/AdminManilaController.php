<?php

namespace App\Http\Controllers\admin\area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminManilaController extends Controller
{
    public function AdminAreasPage()
    {
        $firstLocation = DB::table('areas')
            ->orderBy('location_name')
            ->value('location_name');

        if (!$firstLocation) {
            return redirect()->back()->with('error', 'No areas found.');
        }

        return redirect()->route('admin.areas.location.page', ['location' => $firstLocation]);
    }

    public function AdminManilaPage($location)
    {
        $locationAreas = DB::table('areas')
            ->join('collectors', 'collectors.id', '=', 'areas.collector_id')
            ->where('areas.location_name', $location)
            ->select('areas.id', 'areas.areas_name', 'collectors.fullname as collector_name')
            ->get();

        return view('admin.areas.manila.index', [
            'locationAreas' => $locationAreas,
            'location_name' => $location,
        ]);
    }

    public function AdminSalesReportPrint(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'area_id' => 'nullable|exists:areas,id',
            'all_areas' => 'nullable|boolean',
            'location_name' => 'required|string|exists:areas,location_name'
        ]);

        $from = $request->input('from');
        $to = $request->input('to');
        $allAreas = $request->boolean('all_areas');
        $areaId = $request->input('area_id');
        $locationName = $request->input('location_name');

        $locationAreaIds = DB::table('areas')
            ->where('location_name', $locationName)
            ->pluck('id')
            ->toArray();

        $loans = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->join('areas as a', 'c.area_id', '=', 'a.id')
            ->when($allAreas, fn($q) => $q->whereIn('a.id', $locationAreaIds))
            ->when(!$allAreas && $areaId, fn($q) => $q->where('a.id', $areaId))
            ->whereBetween('cl.loan_from', [$from, $to])
            ->select(
                'cl.*',
                'c.fullname',
                'a.areas_name',
                'a.location_name'
            )
            ->orderBy('cl.loan_from', 'asc')
            ->get();

        return view('admin.areas.print.sales_report', compact('loans', 'from', 'to', 'allAreas'));
    }
}
