<?php

namespace App\Http\Controllers\secretary\area;

use App\Http\Controllers\Controller;
use App\Models\Areas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class SecretaryAreaController extends Controller
{
    public function SecretaryAreasPage()
    {
        $secretary = Session::get('user');
        $secretaryId = $secretary->id;

        $areas = Areas::where('secretary_id', $secretaryId)->get()->sortBy('areas_name', SORT_NATURAL);

        return view('secretary.areas.index', compact('areas'));
    }

    public function SecretarySalesReportPrint(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'area_id' => 'nullable|exists:areas,id',
            'all_areas' => 'nullable|boolean'
        ]);

        $secretary = Session::get('user');
        $secretaryId = $secretary->id;

        $from = $request->input('from');
        $to = $request->input('to');
        $allAreas = $request->boolean('all_areas');
        $areaId = $request->input('area_id');

        if ($allAreas) {
            $myUniqueAreas = DB::table('areas')
                ->where('secretary_id', $secretaryId)
                ->select('location_name', 'areas_name')
                ->distinct()
                ->get();

            $matchedAreaIds = DB::table('areas')
                ->where(function($query) use ($myUniqueAreas) {
                    foreach ($myUniqueAreas as $ua) {
                        $query->orWhere(function($q) use ($ua) {
                            $q->where('location_name', $ua->location_name)
                              ->where('areas_name', $ua->areas_name);
                        });
                    }
                })
                ->pluck('id')
                ->toArray();
        } else {
            $selectedArea = DB::table('areas')
                ->where('id', $areaId)
                ->first();

            $matchedAreaIds = $selectedArea ? DB::table('areas')
                ->where('location_name', $selectedArea->location_name)
                ->where('areas_name', $selectedArea->areas_name)
                ->pluck('id')
                ->toArray() : [];
        }

        $loans = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->join('areas as a', 'c.area_id', '=', 'a.id')
            ->whereIn('a.id', $matchedAreaIds)
            ->whereBetween('cl.loan_from', [$from, $to])
            ->select(
                'cl.*',
                'c.fullname',
                'a.areas_name',
                'a.location_name'
            )
            ->orderBy('cl.loan_from', 'asc')
            ->get();

        return view('secretary.areas.print.sales_report', compact('loans', 'from', 'to', 'allAreas'));
    }
}
