<?php

namespace App\Http\Controllers\management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ManagementAreaController extends Controller
{
    public function ManagementAreasPage()
    {
        $management = Session::get('user');
        if (!$management) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $firstLocation = DB::table('areas')
            ->orderBy('location_name')
            ->value('location_name');

        if (!$firstLocation) {
            return redirect()->back()->with('error', 'No areas found.');
        }

        return redirect()->route('management.areas.location.page', ['location' => $firstLocation]);
    }

    public function ManagementManilaPage($location)
    {
        $management = Session::get('user');
        if (!$management) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $locationAreas = DB::table('areas')
            ->join('collectors', 'collectors.id', '=', 'areas.collector_id')
            ->where('areas.location_name', $location)
            ->select('areas.id', 'areas.areas_name', 'collectors.fullname as collector_name')
            ->get()
            ->sortBy('areas_name', SORT_NATURAL);

        return view('management.areas.index', [
            'locationAreas' => $locationAreas,
            'location_name' => $location,
        ]);
    }

    public function ManagementCollectionReferencesPage($areaId)
    {
        $management = Session::get('user');
        if (!$management) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $area = DB::table('areas')
            ->where('id', $areaId)
            ->first();

        if (!$area) {
            return redirect()->back()->with('error', 'Area not found.');
        }

        $matchedAreaIds = DB::table('areas')
            ->where('location_name', $area->location_name)
            ->where('areas_name', $area->areas_name)
            ->pluck('id')
            ->toArray();

        $references = DB::table('clients_payments as cp')
            ->leftJoin('collectors as col', 'cp.collected_by', '=', 'col.id')
            ->whereIn('cp.client_area', $matchedAreaIds)
            ->select(
                'cp.reference_number',
                'cp.due_date',
                'cp.collected_by',
                'col.fullname as collected_by_name'
            )
            ->groupBy('cp.reference_number', 'cp.due_date', 'cp.collected_by', 'col.fullname')
            ->orderBy('cp.due_date', 'desc')
            ->get();

        $references = $references->map(function ($ref) use ($matchedAreaIds) {
            $loans = DB::table('clients_loans as cl')
                ->join('clients as c', 'cl.client_id', '=', 'c.id')
                ->whereIn('c.area_id', $matchedAreaIds)
                ->whereDate('cl.loan_from', '<=', $ref->due_date)
                ->select('cl.*', 'c.id as client_id')
                ->get();

            $payments = DB::table('clients_payments')
                ->whereIn('client_area', $matchedAreaIds)
                ->where('reference_number', $ref->reference_number)
                ->get()
                ->keyBy('client_loans_id');

            $filteredClients = $loans->filter(function ($loan) use ($payments) {
                $balance = $loan->balance ?? 0;
                $payment = $payments[$loan->id] ?? null;

                return $balance > 0 || ($balance <= 0 && $payment && ($payment->collection ?? 0) > 0);
            });

            $ref->total_clients = $filteredClients->count();

            $ref->total_collections = $filteredClients->sum(function ($loan) use ($payments) {
                $payment = $payments[$loan->id] ?? null;
                return $payment ? ($payment->collection ?? 0) : 0;
            });

            $ref->total_daily_collectibles = $filteredClients->sum(function ($loan) {
                return $loan->daily ?? 0;
            });

            return $ref;
        });

        return view('management.areas.collections_references', [
            'references' => $references,
            'areaId' => $areaId,
            'location_name' => $area->location_name ?? 'N/A',
            'areas_name' => $area->areas_name ?? 'N/A'
        ]);
    }

    public function ManagementCollectionDetailPage($referenceNumber)
    {
        $management = Session::get('user');
        if (!$management) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $reference = DB::table('clients_payments')
            ->where('reference_number', $referenceNumber)
            ->first();

        if (!$reference) {
            return redirect()->back()->with('error', 'Reference not found.');
        }

        $area = DB::table('areas')
            ->where('id', $reference->client_area)
            ->first();

        if (!$area) {
            return redirect()->back()->with('error', 'Area not found.');
        }

        $selectedDate = $reference->due_date;

        $matchedAreaIds = DB::table('areas')
            ->where('location_name', $area->location_name)
            ->where('areas_name', $area->areas_name)
            ->pluck('id')
            ->toArray();

        $loans = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->whereIn('c.area_id', $matchedAreaIds)
            ->whereDate('cl.loan_from', '<=', $selectedDate)
            ->select(
                'cl.*',
                'c.fullname',
                'c.id as client_id'
            )
            ->orderByDesc('cl.id')
            ->get();

        $payments = DB::table('clients_payments')
            ->where('reference_number', $referenceNumber)
            ->get()
            ->keyBy('client_loans_id');

        $clients = $loans->map(function ($loan) use ($payments, $selectedDate) {
            $payment = $payments[$loan->id] ?? null;

            $isOverdue = \Carbon\Carbon::parse($selectedDate)
                ->gt(\Carbon\Carbon::parse($loan->loan_to));

            // Calculate running/cumulative collection up to $selectedDate (inclusive)
            $cumulativePaid = DB::table('clients_payments')
                ->where('client_loans_id', $loan->id)
                ->whereDate('due_date', '<=', $selectedDate)
                ->where('is_collected', 1)
                ->sum('collection');

            $outstandingBalance = max(0, ($loan->loan_amount ?? 0) - $cumulativePaid);

            // Balance Should Be
            $dueDate = \Carbon\Carbon::parse($selectedDate);
            $loanStart = \Carbon\Carbon::parse($loan->loan_from);
            $days = $dueDate->lessThan($loanStart) ? 0 : ($loanStart->diffInDays($dueDate, false) + 1);
            $balanceShouldBe = max(0, ($loan->loan_amount ?? 0) - $days * ($loan->daily ?? 0));

            $overdueVal = 0;
            $isPaid = ($loan->balance ?? 0) <= 0 || $outstandingBalance <= 0;
            if (!$isPaid) {
                $overdueVal = max(0, $outstandingBalance - $balanceShouldBe);
            }

            // Old balance (before today's payment)
            $oldBalanceDisplay = $payment ? ($payment->old_balance ?? $loan->balance) : $loan->balance;

            // Outstanding balance
            if ($payment) {
                $colAmt = is_numeric($payment->collection) ? (float)$payment->collection : 0.0;
                $isCollectedToday = ($payment->is_collected == 1);
                if ($isCollectedToday) {
                    $outstandingBalanceDisplay = $loan->balance;
                } else {
                    $outstandingBalanceDisplay = max(0, $loan->balance - $colAmt);
                }
            } else {
                $outstandingBalanceDisplay = $loan->balance;
            }

            return (object)[
                'id' => $loan->client_id,
                'fullname' => $loan->fullname,
                'loan' => $loan,
                'payment' => $payment,
                'is_overdue' => $isOverdue,
                'overdueVal' => $overdueVal,
                'oldBalanceDisplay' => $oldBalanceDisplay,
                'outstandingBalanceDisplay' => $outstandingBalanceDisplay
            ];
        });

        $clients = $clients->filter(function ($c) {
            $balance = $c->loan->balance ?? 0;
            return $balance > 0 || ($balance <= 0 && $c->payment && ($c->payment->collection ?? 0) > 0);
        })->values();

        $totalClients = $clients->count();
        $totalCollections = $clients->sum(function ($c) {
            return $c->payment->collection ?? 0;
        });
        $totalDailyCollectibles = $clients->sum(function ($c) {
            return $c->loan->daily ?? 0;
        });

        return view('management.areas.collection_detail', [
            'clients' => $clients,
            'referenceNumber' => $referenceNumber,
            'location_name' => $area->location_name ?? 'N/A',
            'areas_name' => $area->areas_name ?? 'N/A',
            'totalClients' => $totalClients,
            'totalCollections' => $totalCollections,
            'totalDailyCollectibles' => $totalDailyCollectibles,
            'selectedDate' => $selectedDate,
            'refNo' => $referenceNumber,
            'areaId' => $area->id
        ]);
    }

    public function ManagementPrintCollection($refNo)
    {
        $reference = DB::table('clients_payments')
            ->where('reference_number', $refNo)
            ->first();

        if (!$reference) {
            abort(404, 'Reference not found.');
        }

        $area = DB::table('areas')
            ->where('id', $reference->client_area)
            ->first();

        if (!$area) {
            abort(404, 'Area not found.');
        }

        $payments = DB::table('clients_payments as cp')
            ->join('clients as c', 'cp.client_id', '=', 'c.id')
            ->join('clients_loans as cl', 'cp.client_loans_id', '=', 'cl.id')
            ->leftJoin('collectors as col', 'cp.collected_by', '=', 'col.id')
            ->where('cp.reference_number', $refNo)
            ->select(
                'cp.*',
                'c.fullname',
                'cl.loan_amount',
                'cl.balance',
                'cl.loan_to',
                'cl.loan_from',
                'cl.daily as cl_daily',
                'col.fullname as collected_by_name'
            )
            ->orderBy('c.fullname')
            ->get();

        if ($payments->isEmpty()) {
            abort(404, 'No payments found.');
        }

        foreach ($payments as $payment) {
            $selectedDate = $payment->due_date;
            
            // Calculate running/cumulative collection up to $selectedDate (inclusive)
            $cumulativePaid = DB::table('clients_payments')
                ->where('client_loans_id', $payment->client_loans_id)
                ->whereDate('due_date', '<=', $selectedDate)
                ->where('is_collected', 1)
                ->sum('collection');

            $outstandingBalance = max(0, ($payment->loan_amount ?? 0) - $cumulativePaid);

            // Balance Should Be
            $dueDate = \Carbon\Carbon::parse($selectedDate);
            $loanStart = \Carbon\Carbon::parse($payment->loan_from);
            $days = $dueDate->lessThan($loanStart) ? 0 : ($loanStart->diffInDays($dueDate, false) + 1);
            $balanceShouldBe = max(0, ($payment->loan_amount ?? 0) - $days * ($payment->cl_daily ?? 0));

            $overdueVal = 0;
            $isPaid = ($payment->balance ?? 0) <= 0 || $outstandingBalance <= 0;
            if (!$isPaid) {
                $overdueVal = max(0, $outstandingBalance - $balanceShouldBe);
            }

            // Old balance (before today's payment)
            $oldBalanceDisplay = $payment->old_balance ?? $payment->balance;

            // Outstanding balance (after today's payment if collected/entered)
            $colAmt = is_numeric($payment->collection) ? (float)$payment->collection : 0.0;
            $isCollectedToday = ($payment->is_collected == 1);
            if ($isCollectedToday) {
                $outstandingBalanceDisplay = $payment->balance;
            } else {
                $outstandingBalanceDisplay = max(0, $payment->balance - $colAmt);
            }

            $payment->balanceShouldBe = $balanceShouldBe;
            $payment->overdueVal = $overdueVal;
            $payment->oldBalanceDisplay = $oldBalanceDisplay;
            $payment->outstandingBalanceDisplay = $outstandingBalanceDisplay;
        }

        return view('management.areas.print.print_collection', [
            'payments' => $payments,
            'area' => $area,
            'referenceNumber' => $refNo
        ]);
    }

}
