<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function AdminDashboardPage(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $isFiltered = $from
            && $to
            && Carbon::hasFormat($from, 'Y-m-d')
            && Carbon::hasFormat($to, 'Y-m-d');

        $displayFrom = $isFiltered ? $from : null;
        $displayTo = $isFiltered ? $to : null;
        $fromDateTime = $isFiltered ? Carbon::parse($from)->startOfDay() : null;
        $toDateTime = $isFiltered ? Carbon::parse($to)->endOfDay() : null;

        $areas = DB::table('areas')
            ->select('id', 'location_name', 'areas_name')
            ->orderBy('location_name')
            ->orderBy('areas_name')
            ->get();

        $today = Carbon::today()->toDateString();

        $loanByArea = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->when($isFiltered, fn($query) => $query->whereBetween('cl.loan_from', [$from, $to]))
            ->selectRaw('c.area_id,
                COUNT(*) as total_loans,
                COUNT(DISTINCT cl.client_id) as unique_clients,
                SUM(CASE WHEN cl.loan_status = "new" THEN 1 ELSE 0 END) as new_loan_count,
                SUM(CASE WHEN cl.loan_status = "renewal" THEN 1 ELSE 0 END) as renewal_loan_count,
                SUM(cl.loan_amount) as total_loans_amount,
                SUM(cl.balance) as total_balance,
                SUM(CASE WHEN cl.loan_to < ? AND cl.balance > 0 THEN cl.balance ELSE 0 END) as total_lapsed_balance,
                SUM(CASE WHEN cl.loan_to >= ? AND cl.balance > 0 THEN cl.balance ELSE 0 END) as total_active_balance', [$today, $today])
            ->groupBy('c.area_id')
            ->get()
            ->keyBy('area_id');

        $paymentByArea = DB::table('clients_payments as cp')
            ->when($isFiltered, fn($query) => $query->whereBetween('cp.due_date', [$from, $to]))
            ->selectRaw('cp.client_area as area_id, SUM(cp.daily) as total_collectibles, SUM(CASE WHEN cp.is_collected = 1 THEN cp.collection ELSE 0 END) as total_collected, COUNT(*) as payment_count')
            ->groupBy('cp.client_area')
            ->get()
            ->keyBy('area_id');

        $newClientsByArea = DB::table('clients')
            ->when($isFiltered, fn($query) => $query->whereBetween('created_at', [$fromDateTime, $toDateTime]))
            ->selectRaw('area_id, COUNT(*) as new_clients')
            ->groupBy('area_id')
            ->get()
            ->keyBy('area_id');

        $areaSummaries = $areas->map(function ($area) use ($loanByArea, $paymentByArea, $newClientsByArea) {
            $loan = $loanByArea->get($area->id);
            $payment = $paymentByArea->get($area->id);
            $newClients = $newClientsByArea->get($area->id);

            return (object) [
                'id' => $area->id,
                'location_name' => $area->location_name,
                'areas_name' => $area->areas_name,
                'total_clients' => (int) ($loan->unique_clients ?? 0),
                'new_clients' => (int) ($newClients->new_clients ?? 0),
                'total_loans' => (int) ($loan->total_loans ?? 0),
                'new_loan_count' => (int) ($loan->new_loan_count ?? 0),
                'renewal_loan_count' => (int) ($loan->renewal_loan_count ?? 0),
                'total_loans_amount' => (float) ($loan->total_loans_amount ?? 0),
                'total_balance' => (float) ($loan->total_balance ?? 0),
                'total_lapsed_balance' => (float) ($loan->total_lapsed_balance ?? 0),
                'total_active_balance' => (float) ($loan->total_active_balance ?? 0),
                'total_collectibles' => (float) ($payment->total_collectibles ?? 0),
                'total_collected' => (float) ($payment->total_collected ?? 0),
                'payment_count' => (int) ($payment->payment_count ?? 0),
            ];
        });

        $locationSummaries = $areaSummaries
            ->groupBy('location_name')
            ->map(function ($group, $location) {
                return (object) [
                    'location_name' => $location,
                    'total_clients' => $group->sum('total_clients'),
                    'new_clients' => $group->sum('new_clients'),
                    'total_loans' => $group->sum('total_loans'),
                    'new_loan_count' => $group->sum('new_loan_count'),
                    'renewal_loan_count' => $group->sum('renewal_loan_count'),
                    'total_loans_amount' => $group->sum('total_loans_amount'),
                    'total_balance' => $group->sum('total_balance'),
                    'total_lapsed_balance' => $group->sum('total_lapsed_balance'),
                    'total_active_balance' => $group->sum('total_active_balance'),
                    'total_collectibles' => $group->sum('total_collectibles'),
                    'total_collected' => $group->sum('total_collected'),
                ];
            })
            ->values();

        $overall = [
            'locations' => $locationSummaries->count(),
            'areas' => $areaSummaries->count(),
            'total_clients' => (int) $areaSummaries->sum('total_clients'),
            'new_clients' => (int) $areaSummaries->sum('new_clients'),
            'total_loans' => (int) $areaSummaries->sum('total_loans'),
            'total_loans_amount' => (float) $areaSummaries->sum('total_loans_amount'),
            'total_balance' => (float) $areaSummaries->sum('total_balance'),
            'total_lapsed_balance' => (float) $areaSummaries->sum('total_lapsed_balance'),
            'total_active_balance' => (float) $areaSummaries->sum('total_active_balance'),
            'total_collectibles' => (float) $areaSummaries->sum('total_collectibles'),
            'total_collected' => (float) $areaSummaries->sum('total_collected'),
        ];

        $loanStatus = DB::table('clients_loans')
            ->when($isFiltered, fn($query) => $query->whereBetween('loan_from', [$from, $to]))
            ->selectRaw('COALESCE(loan_status, "unknown") as label, COUNT(*) as value')
            ->groupBy('loan_status')
            ->get();

        $paymentType = DB::table('clients_payments')
            ->when($isFiltered, fn($query) => $query->whereBetween('due_date', [$from, $to]))
            ->selectRaw('COALESCE(type, "untyped") as label, COUNT(*) as value')
            ->groupBy('type')
            ->get();

        $charts = [
            'locationLabels' => $locationSummaries->pluck('location_name')->values(),
            'locationLoans' => $locationSummaries->pluck('total_loans_amount')->map(fn($v) => (float) $v)->values(),
            'locationCollected' => $locationSummaries->pluck('total_collected')->map(fn($v) => (float) $v)->values(),
            'areaLabels' => $areaSummaries->pluck('areas_name')->values(),
            'areaCollections' => $areaSummaries->pluck('total_collected')->map(fn($v) => (float) $v)->values(),
            'areaLoans' => $areaSummaries->pluck('total_loans_amount')->map(fn($v) => (float) $v)->values(),
            'loanStatusLabels' => $loanStatus->pluck('label')->values(),
            'loanStatusValues' => $loanStatus->pluck('value')->map(fn($v) => (int) $v)->values(),
            'paymentTypeLabels' => $paymentType->pluck('label')->values(),
            'paymentTypeValues' => $paymentType->pluck('value')->map(fn($v) => (int) $v)->values(),
        ];

        return view('admin.dashboard.index', compact(
            'from',
            'to',
            'displayFrom',
            'displayTo',
            'isFiltered',
            'overall',
            'areaSummaries',
            'locationSummaries',
            'charts'
        ));
    }
}
