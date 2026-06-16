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

        $areas = Areas::where('secretary_id', $secretaryId)->get();

        $location_name = $areas->first()->location_name ?? 'No Location';

        return view('secretary.areas.index', compact('areas', 'location_name'));
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

        $secretaryAreaIds = Areas::where('secretary_id', $secretaryId)->pluck('id')->toArray();

        $loans = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->join('areas as a', 'c.area_id', '=', 'a.id')
            ->when($allAreas, fn($q) => $q->whereIn('a.id', $secretaryAreaIds))
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

        return view('secretary.areas.print.sales_report', compact('loans', 'from', 'to', 'allAreas'));
    }
}
