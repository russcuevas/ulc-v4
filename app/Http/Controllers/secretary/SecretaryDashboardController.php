<?php

namespace App\Http\Controllers\secretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use Carbon\Carbon;

class SecretaryDashboardController extends Controller
{
    public function SecretaryDashboardPage(Request $request)
    {
        $secretary = Session::get('user');
        if (!$secretary) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $secretaryId = $secretary->id;

        $from = $request->query('from');
        $to = $request->query('to');

        $showAllTime = false;
        if (!$from && !$to) {
            $showAllTime = true;
        } else {
            if (!Carbon::hasFormat($from, 'Y-m-d') || !Carbon::hasFormat($to, 'Y-m-d')) {
                $from = Carbon::now()->startOfMonth()->format('Y-m-d');
                $to = Carbon::now()->endOfMonth()->format('Y-m-d');
            }
        }

        $areas = DB::table('areas')
            ->where('secretary_id', $secretaryId)
            ->get();

        $areaSummaries = $areas->map(function ($area) use ($from, $to, $showAllTime) {
            $totalLoansQuery = DB::table('clients_loans as cl')
                ->join('clients as c', 'cl.client_id', '=', 'c.id')
                ->where('c.area_id', $area->id);
            if (!$showAllTime) {
                $totalLoansQuery->whereBetween('cl.loan_from', [$from, $to]);
            }
            $totalLoans = $totalLoansQuery->count();

            $totalLoansAmountQuery = DB::table('clients_loans as cl')
                ->join('clients as c', 'cl.client_id', '=', 'c.id')
                ->where('c.area_id', $area->id);
            if (!$showAllTime) {
                $totalLoansAmountQuery->whereBetween('cl.loan_from', [$from, $to]);
            }
            $totalLoansAmount = $totalLoansAmountQuery->sum('cl.loan_amount');

            $totalCollectedQuery = DB::table('clients_payments')
                ->where('client_area', $area->id)
                ->where('is_collected', 1);
            $totalCollectiblesQuery = DB::table('clients_payments')
                ->where('client_area', $area->id);

            if (!$showAllTime) {
                $totalCollectedQuery->whereBetween('due_date', [$from, $to]);
                $totalCollectiblesQuery->whereBetween('due_date', [$from, $to]);
            }

            $totalCollected = $totalCollectedQuery->sum('collection');
            $totalCollectibles = $totalCollectiblesQuery->sum('daily');

            return (object)[
                'id' => $area->id,
                'areas_name' => $area->areas_name,
                'location_name' => $area->location_name,
                'total_loans' => $totalLoans,
                'total_loans_amount' => $totalLoansAmount,
                'total_collectibles' => $totalCollectibles,
                'total_collected' => $totalCollected,
            ];
        });

        return view('secretary.dashboard.index', compact('areaSummaries', 'from', 'to', 'showAllTime'));
    }
}
