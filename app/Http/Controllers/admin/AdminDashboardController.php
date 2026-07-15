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
        $selectedArea = $request->query('area');

        $isFiltered = $from
            && $to
            && Carbon::hasFormat($from, 'Y-m-d')
            && Carbon::hasFormat($to, 'Y-m-d');

        $displayFrom = $isFiltered ? $from : null;
        $displayTo = $isFiltered ? $to : null;
        $fromDateTime = $isFiltered ? Carbon::parse($from)->startOfDay() : null;
        $toDateTime = $isFiltered ? Carbon::parse($to)->endOfDay() : null;

        $filteredLocation = null;
        $filteredAreaName = null;
        if ($selectedArea && str_contains($selectedArea, '|')) {
            [$filteredLocation, $filteredAreaName] = explode('|', $selectedArea);
        }

        $areas = DB::table('areas')
            ->select('location_name', 'areas_name')
            ->groupBy('location_name', 'areas_name')
            ->orderBy('location_name')
            ->orderBy('areas_name')
            ->get()
            ->sortBy('areas_name', SORT_NATURAL);

        $today = Carbon::today()->toDateString();

        $loanByArea = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->join('areas as a', 'c.area_id', '=', 'a.id')
            ->when($isFiltered, fn($query) => $query->whereBetween('cl.loan_from', [$from, $to]))
            ->selectRaw('a.location_name, a.areas_name,
                COUNT(*) as total_loans,
                COUNT(DISTINCT cl.client_id) as unique_clients,
                SUM(CASE WHEN cl.loan_status = "new" THEN 1 ELSE 0 END) as new_loan_count,
                SUM(CASE WHEN cl.loan_status = "renewal" THEN 1 ELSE 0 END) as renewal_loan_count,
                SUM(cl.loan_amount) as total_loans_amount,
                SUM(cl.balance) as total_balance,
                SUM(CASE WHEN cl.loan_to < ? AND cl.balance > 0 THEN cl.balance ELSE 0 END) as total_lapsed_balance,
                SUM(CASE WHEN cl.loan_to >= ? AND cl.balance > 0 THEN cl.balance ELSE 0 END) as total_active_balance', [$today, $today])
            ->groupBy('a.location_name', 'a.areas_name')
            ->get()
            ->keyBy(fn($item) => $item->location_name . '|' . $item->areas_name);

        $paymentByArea = DB::table('clients_payments as cp')
            ->join('areas as a', 'cp.client_area', '=', 'a.id')
            ->when($isFiltered, fn($query) => $query->whereBetween('cp.due_date', [$from, $to]))
            ->selectRaw('a.location_name, a.areas_name, SUM(cp.daily) as total_collectibles, SUM(CASE WHEN cp.is_collected = 1 THEN cp.collection ELSE 0 END) as total_collected, COUNT(*) as payment_count')
            ->groupBy('a.location_name', 'a.areas_name')
            ->get()
            ->keyBy(fn($item) => $item->location_name . '|' . $item->areas_name);

        $newClientsByArea = DB::table('clients as c')
            ->join('areas as a', 'c.area_id', '=', 'a.id')
            ->when($isFiltered, fn($query) => $query->whereBetween('c.created_at', [$fromDateTime, $toDateTime]))
            ->selectRaw('a.location_name, a.areas_name, COUNT(*) as new_clients')
            ->groupBy('a.location_name', 'a.areas_name')
            ->get()
            ->keyBy(fn($item) => $item->location_name . '|' . $item->areas_name);

        $areaSummaries = $areas->map(function ($area) use ($loanByArea, $paymentByArea, $newClientsByArea) {
            $key = $area->location_name . '|' . $area->areas_name;
            $loan = $loanByArea->get($key);
            $payment = $paymentByArea->get($key);
            $newClients = $newClientsByArea->get($key);

            $firstId = DB::table('areas')
                ->where('location_name', $area->location_name)
                ->where('areas_name', $area->areas_name)
                ->value('id');

            return (object) [
                'id' => $firstId,
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

        // Create filtered sets for dashboard cards and charts
        $dashboardAreaSummaries = $areaSummaries;
        if ($filteredLocation && $filteredAreaName) {
            $dashboardAreaSummaries = $areaSummaries->filter(function ($area) use ($filteredLocation, $filteredAreaName) {
                return $area->location_name === $filteredLocation && $area->areas_name === $filteredAreaName;
            });
        }

        $dashboardLocationSummaries = $dashboardAreaSummaries
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
            'locations' => $dashboardLocationSummaries->count(),
            'areas' => $dashboardAreaSummaries->count(),
            'total_clients' => (int) $dashboardAreaSummaries->sum('total_clients'),
            'new_clients' => (int) $dashboardAreaSummaries->sum('new_clients'),
            'total_loans' => (int) $dashboardAreaSummaries->sum('total_loans'),
            'total_loans_amount' => (float) $dashboardAreaSummaries->sum('total_loans_amount'),
            'total_balance' => (float) $dashboardAreaSummaries->sum('total_balance'),
            'total_lapsed_balance' => (float) $dashboardAreaSummaries->sum('total_lapsed_balance'),
            'total_active_balance' => (float) $dashboardAreaSummaries->sum('total_active_balance'),
            'total_collectibles' => (float) $dashboardAreaSummaries->sum('total_collectibles'),
            'total_collected' => (float) $dashboardAreaSummaries->sum('total_collected'),
        ];

        $loanStatus = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->join('areas as a', 'c.area_id', '=', 'a.id')
            ->when($isFiltered, fn($query) => $query->whereBetween('cl.loan_from', [$from, $to]))
            ->when($filteredLocation && $filteredAreaName, fn($query) => $query->where('a.location_name', $filteredLocation)->where('a.areas_name', $filteredAreaName))
            ->selectRaw('COALESCE(cl.loan_status, "unknown") as label, COUNT(*) as value')
            ->groupBy('cl.loan_status')
            ->get();

        $paymentType = DB::table('clients_payments as cp')
            ->join('areas as a', 'cp.client_area', '=', 'a.id')
            ->when($isFiltered, fn($query) => $query->whereBetween('cp.due_date', [$from, $to]))
            ->when($filteredLocation && $filteredAreaName, fn($query) => $query->where('a.location_name', $filteredLocation)->where('a.areas_name', $filteredAreaName))
            ->selectRaw('COALESCE(cp.type, "untyped") as label, COUNT(*) as value')
            ->groupBy('cp.type')
            ->get();

        $charts = [
            'locationLabels' => $dashboardLocationSummaries->pluck('location_name')->values(),
            'locationLoans' => $dashboardLocationSummaries->pluck('total_loans_amount')->map(fn($v) => (float) $v)->values(),
            'locationCollected' => $dashboardLocationSummaries->pluck('total_collected')->map(fn($v) => (float) $v)->values(),
            'areaLabels' => $dashboardAreaSummaries->pluck('areas_name')->values(),
            'areaCollections' => $dashboardAreaSummaries->pluck('total_collected')->map(fn($v) => (float) $v)->values(),
            'areaLoans' => $dashboardAreaSummaries->pluck('total_loans_amount')->map(fn($v) => (float) $v)->values(),
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
            'charts',
            'areas',
            'selectedArea'
        ));
    }

    public function getPaymentDetails(Request $request)
    {
        $type = $request->query('type'); // 'location' or 'area'
        $name = $request->query('name'); // e.g. 'Caloocan Area' or 'CA1'
        $from = $request->query('from');
        $to = $request->query('to');

        $isFiltered = $from
            && $to
            && Carbon::hasFormat($from, 'Y-m-d')
            && Carbon::hasFormat($to, 'Y-m-d');

        $query = DB::table('clients_payments as cp')
            ->join('clients as c', 'cp.client_id', '=', 'c.id')
            ->join('areas as a', 'cp.client_area', '=', 'a.id')
            ->leftJoin('collectors as col', 'cp.collected_by', '=', 'col.id')
            ->where('cp.is_collected', 1)
            ->when($isFiltered, fn($q) => $q->whereBetween('cp.due_date', [$from, $to]));

        if ($type === 'location') {
            $query->where('a.location_name', $name);
        } else {
            $query->where('a.areas_name', $name);
        }

        $payments = $query->select([
            'c.fullname as client_name',
            'a.location_name',
            'a.areas_name',
            'cp.collection as amount',
            'cp.due_date',
            'cp.created_at',
            'col.fullname as collector_name'
        ])
        ->orderBy('cp.due_date', 'desc')
        ->orderBy('c.fullname', 'asc')
        ->get();

        return response()->json($payments);
    }
}

