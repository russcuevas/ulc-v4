<?php

namespace App\Http\Controllers\admin\area;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Collector;
use App\Models\Secretary;
use App\Notifications\NewClientNotification;

class AdminManilaClientsController extends Controller
{
    public function AdminManilaClientsPage($id)
    {
        $area = DB::table('areas')
            ->where('id', $id)
            ->select('areas_name', 'location_name')
            ->first();

        $areas_name = $area->areas_name ?? 'Unknown Area';
        $location_name = $area->location_name ?? 'Unknown Location';

        $matchedAreaIds = DB::table('areas')
            ->where('location_name', $location_name)
            ->where('areas_name', $areas_name)
            ->pluck('id')
            ->toArray();

        $clients = Clients::whereIn('area_id', $matchedAreaIds)->get();

        $allAreas = DB::table('areas')
            ->select(DB::raw('MIN(id) as id'), 'location_name', 'areas_name')
            ->groupBy('location_name', 'areas_name')
            ->orderBy('location_name')
            ->orderBy('areas_name')
            ->get()
            ->sortBy('areas_name', SORT_NATURAL);

        // Pass the current area ID and all areas to the view
        return view('admin.areas.manila.clients', compact('clients', 'areas_name', 'location_name', 'id', 'allAreas'));
    }

    public function AdminManilaAddClientRequest(Request $request, $id)
    {
        // Validate input
        $request->validate([
            'fullname'       => 'required|string|max:255',
            'phone'          => 'required|digits:11',
            'phone_number_2' => 'nullable|digits:11',
            'area_id'        => "required|exists:areas,id|in:$id", // must be exactly the current area ID
            'gender'         => 'required|string',
            'loan_from'      => 'required|date',
            'loan_to'        => 'required|date|after_or_equal:loan_from',
            'loan_amount'    => 'required|numeric|min:1',
            'balance'        => 'required|numeric|min:0',
            'daily'          => 'nullable|numeric|min:0',
            'loan_terms'     => 'required|numeric',
            'pn_number'      => 'required|string|unique:clients_loans,pn_number',
            'release_number' => 'required|string|unique:clients_loans,release_number',
        ]);


        DB::transaction(function () use ($request) {

            $clientId = DB::table('clients')->insertGetId([
                'fullname'   => $request->fullname,
                'phone'      => $request->phone,
                'phone_number_2' => $request->phone_number_2,
                'area_id'    => $request->area_id,
                'gender'     => $request->gender,
                'created_by' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('clients_loans')->insert([
                'client_id'      => $clientId,
                'pn_number'      => $request->pn_number,
                'release_number' => $request->release_number,
                'loan_from'      => $request->loan_from,
                'loan_to'        => $request->loan_to,
                'loan_amount'    => $request->loan_amount,
                'balance'        => $request->balance,
                'daily'          => $request->daily,
                'principal'      => $request->loan_amount,
                'loan_terms'     => $request->loan_terms,
                'loan_status'    => 'new',
                'status' => 'unpaid',
                'created_by'     => 'Admin',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            // Create a single shared notification for the area so both admin and secretary see it
            try {
                $client = DB::table('clients')->where('id', $clientId)->first();
                $areaId = $client->area_id ?? null;
                DB::table('area_notifications')->insert([
                    'area_id' => $areaId,
                    'type' => 'new_client',
                    'data' => json_encode([
                        'client_id' => $clientId,
                        'message' => 'New client added: ' . ($client->fullname ?? 'Client'),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Do not block on notification failure
            }
        });

        return redirect()->back()->with('success', 'Client added successfully.');
    }

    public function AdminManilaViewClientLoans($id)
    {
        $client = DB::table('clients')
            ->where('id', $id)
            ->first();

        if (!$client) {
            return redirect()->back()->with('error', 'Client not found.');
        }

        $area = DB::table('areas')
            ->where('id', $client->area_id)
            ->select('areas_name', 'location_name')
            ->first();

        $areas_name = $area->areas_name ?? 'Unknown Area';
        $location_name = $area->location_name ?? 'Unknown Location';

        $loans = DB::table('clients_loans')
            ->where('client_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $allAreas = DB::table('areas')
            ->select(DB::raw('MIN(id) as id'), 'location_name', 'areas_name')
            ->groupBy('location_name', 'areas_name')
            ->orderBy('location_name')
            ->orderBy('areas_name')
            ->get()
            ->sortBy('areas_name', SORT_NATURAL);

        return view('admin.areas.manila.view_loans', compact('areas_name', 'location_name', 'client', 'loans', 'allAreas'));
    }

    public function AdminManilaUpdateClientRequest(Request $request, $id)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'phone_number_2' => 'nullable|string|max:20',
            'gender' => 'required|string',
            'area_id' => 'nullable|exists:areas,id',
            // Loan validation if loan_id is present
            'loan_id' => 'nullable|exists:clients_loans,id',
            'pn_number' => 'required_with:loan_id|string|unique:clients_loans,pn_number,' . $request->loan_id,
            'release_number' => 'required_with:loan_id|string|unique:clients_loans,release_number,' . $request->loan_id,
            'loan_from' => 'required_with:loan_id|date',
            'loan_to' => 'required_with:loan_id|date|after_or_equal:loan_from',
            'loan_amount' => 'required_with:loan_id|numeric|min:1',
            'balance' => 'required_with:loan_id|numeric|min:0',
            'daily' => 'required_with:loan_id|numeric|min:0',
            'loan_terms' => 'required_with:loan_id|numeric|min:1',
        ]);

        DB::transaction(function () use ($request, $id) {
            $client = Clients::findOrFail($id);
            $clientUpdate = [
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'phone_number_2' => $request->phone_number_2,
                'gender' => $request->gender,
            ];

            if ($request->filled('area_id')) {
                $clientUpdate['area_id'] = $request->area_id;
            }

            $client->update($clientUpdate);

            if ($request->has('loan_id')) {
                DB::table('clients_loans')
                    ->where('id', $request->loan_id)
                    ->update([
                        'pn_number' => $request->pn_number,
                        'release_number' => $request->release_number,
                        'loan_from' => $request->loan_from,
                        'loan_to' => $request->loan_to,
                        'loan_amount' => $request->loan_amount,
                        'balance' => $request->balance,
                        'daily' => $request->daily,
                        'loan_terms' => $request->loan_terms,
                        'updated_at' => now(),
                    ]);
            }
        });

        return back()->with('success', 'Information updated successfully!');
    }

    public function AdminReassignClientArea(Request $request, $id)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
        ]);

        $client = Clients::findOrFail($id);
        $oldArea = DB::table('areas')->where('id', $client->area_id)->first();
        $newArea = DB::table('areas')->where('id', $request->area_id)->first();

        $oldName = $oldArea ? ($oldArea->location_name . ' - ' . $oldArea->areas_name) : 'Unknown Area';
        $newName = $newArea ? ($newArea->location_name . ' - ' . $newArea->areas_name) : 'Unknown Area';

        $client->update([
            'area_id' => $request->area_id,
        ]);

        try {
            DB::table('area_notifications')->insert([
                'area_id' => $request->area_id,
                'type' => 'client_reassigned',
                'data' => json_encode([
                    'client_id' => $client->id,
                    'message' => "Client {$client->fullname} was transferred from {$oldName} to {$newName}.",
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Do not block on notification failure
        }

        return redirect()->back()->with('success', "Client {$client->fullname} successfully moved to {$newName}! All loan and payment history is preserved.");
    }

    public function AdminManilaSubmitRenewLoan(Request $request, $clientId)
    {
        $request->validate([
            'pn_number'      => 'required|string|unique:clients_loans,pn_number',
            'release_number' => 'required|string|unique:clients_loans,release_number',
            'loan_from'      => 'required|date',
            'loan_to'        => 'required|date|after_or_equal:loan_from',
            'loan_amount'    => 'required|numeric|min:1',
            'balance'        => 'required|numeric|min:0',
            'daily'          => 'required|numeric|min:0',
            'loan_terms'     => 'required|numeric|min:1',
        ]);

        $lastLoan = DB::table('clients_loans')
            ->where('client_id', $clientId)
            ->orderBy('id', 'desc')
            ->first();

        $lastSavings = $lastLoan ? ($lastLoan->savings_balance ?? 0.00) : 0.00;

        DB::table('clients_loans')->insert([
            'client_id'      => $clientId,
            'pn_number'      => $request->pn_number,
            'release_number' => $request->release_number,
            'loan_from'      => $request->loan_from,
            'loan_to'        => $request->loan_to,
            'loan_amount'    => $request->loan_amount,
            'balance'        => $request->balance,
            'daily'          => $request->daily,
            'principal'      => $request->loan_amount,
            'loan_terms'     => $request->loan_terms,
            'savings_balance' => $lastSavings,
            'loan_status'    => 'renewal',
            'status'         => 'unpaid',
            'created_by'     => 'Admin',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->back()->with('success', 'Loan renewed successfully.');
    }

    public function AdminManilaGenerateSOA($loanId)
    {
        // Get loan
        $loan = DB::table('clients_loans')
            ->select(
                'id',
                'client_id',
                'pn_number',
                'release_number',
                'loan_amount',
                'balance',
                'savings_balance',
                'daily',
                'loan_from',
                'loan_to',
                'loan_terms',
            )
            ->where('id', $loanId)
            ->first();

        if (!$loan) {
            return back()->with('error', 'Loan not found.');
        }

        // Get client with area info
        $client = DB::table('clients')
            ->leftJoin('areas', 'clients.area_id', '=', 'areas.id')
            ->where('clients.id', $loan->client_id)
            ->select(
                'clients.*',
                'areas.location_name',
                'areas.areas_name'
            )
            ->first();

        // Get payments
        $payments = DB::table('clients_payments')
            ->where('client_loans_id', $loanId)
            ->where(function ($query) {
                $query->where('is_collected', 1)
                    ->orWhere(function ($q) {
                        $q->where('savings_amount', '>', 0)
                            ->whereNotNull('savings_amount');
                    });
            })
            ->orderBy('due_date', 'asc')
            ->get();


        return view('admin.areas.manila.print.generate_soa', compact(
            'loan',
            'client',
            'payments',
        ));
    }

    public function AdminPrintSummaryLoan($clientId)
    {
        $client = DB::table('clients')
            ->where('id', $clientId)
            ->first();

        if (!$client) {
            return back()->with('error', 'Client not found.');
        }

        $area = DB::table('areas')
            ->where('id', $client->area_id)
            ->first();

        $loans = DB::table('clients_loans')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalDaily = $loans->sum('daily');
        $totalAmount = $loans->sum('loan_amount');
        $newCount = $loans->where('loan_status', 'new')->count();
        $renewalCount = $loans->where('loan_status', 'renewal')->count();

        return view('admin.areas.print.print_summary_loan', compact(
            'loans',
            'client',
            'area',
            'totalDaily',
            'totalAmount',
            'newCount',
            'renewalCount'
        ));
    }

    public function AdminManilaDeleteClient($id)
    {
        $client = Clients::find($id);

        if (!$client) {
            return redirect()->back()->with('error', 'Client not found.');
        }

        DB::transaction(function () use ($id) {
            // Delete related payments
            DB::table('clients_payments')->where('client_id', $id)->delete();

            // Delete related loans
            DB::table('clients_loans')->where('client_id', $id)->delete();

            // Cleanup notifications related to this client
            DB::table('area_notifications')
                ->where('data', 'like', '%"client_id":' . $id . '%')
                ->orWhere('data', 'like', '%"client_id": "' . $id . '"%')
                ->delete();

            // Delete the client
            DB::table('clients')->where('id', $id)->delete();
        });

        return redirect()->back()->with('success', 'Client and all related data deleted successfully.');
    }

    public function AdminManilaBacklogCollections($loanId)
    {
        // Get loan
        $loan = DB::table('clients_loans')
            ->select(
                'id',
                'client_id',
                'pn_number',
                'release_number',
                'loan_amount',
                'balance',
                'savings_balance',
                'daily',
                'loan_from',
                'loan_to',
                'loan_terms',
            )
            ->where('id', $loanId)
            ->first();

        if (!$loan) {
            return back()->with('error', 'Loan not found.');
        }

        // Get client with area info
        $client = DB::table('clients')
            ->leftJoin('areas', 'clients.area_id', '=', 'areas.id')
            ->where('clients.id', $loan->client_id)
            ->select(
                'clients.*',
                'areas.location_name',
                'areas.areas_name'
            )
            ->first();

        // Get payments
        $payments = DB::table('clients_payments')
            ->where('client_loans_id', $loanId)
            ->get()
            ->keyBy('due_date');

        // Generate date list from loan_from to loan_to (term schedule)
        $startDate = \Carbon\Carbon::parse($loan->loan_from);
        $termEndDate = \Carbon\Carbon::parse($loan->loan_to);
        $today = \Carbon\Carbon::now('Asia/Manila')->startOfDay();

        // Find latest payment date beyond termEndDate if any
        $latestPaymentDate = $termEndDate->copy();
        foreach ($payments as $dueDate => $payment) {
            $pDate = \Carbon\Carbon::parse($dueDate)->startOfDay();
            if ($pDate->gt($latestPaymentDate)) {
                $latestPaymentDate = $pDate;
            }
        }

        // Continuous extension:
        // If loan still has remaining balance (> 0), extend up to today (or latest payment date if in future)
        // If loan is fully paid (balance <= 0), extend only up to the latest payment date (or loan_to)
        if ((float)($loan->balance ?? 0) > 0) {
            $maxEndDate = $today->gt($latestPaymentDate) ? $today->copy() : $latestPaymentDate->copy();
            if ($termEndDate->gt($maxEndDate)) {
                $maxEndDate = $termEndDate->copy();
            }
        } else {
            $maxEndDate = $latestPaymentDate->gt($termEndDate) ? $latestPaymentDate->copy() : $termEndDate->copy();
        }

        $dateList = [];
        $tempDate = $startDate->copy();
        while ($tempDate->lte($maxEndDate)) {
            $dateList[] = $tempDate->format('Y-m-d');
            $tempDate->addDay();
        }

        // Include any due_date after loan_to if a payment record exists
        foreach ($payments as $dueDate => $payment) {
            if (!in_array($dueDate, $dateList)) {
                $dateList[] = $dueDate;
            }
        }

        // Sort chronologically
        usort($dateList, function($a, $b) {
            return strcmp($a, $b);
        });

        // Compute grids with pre-generated reference numbers
        $runningPayment = 0;
        $paymentsGrid = [];
        $totalLoanAmount = (float) $loan->loan_amount;
        $dailyRate = (float) ($loan->daily ?? 0);
        $loanTerms = (int) ($loan->loan_terms ?? 100);

        foreach ($dateList as $index => $dateStr) {
            $payment = $payments->get($dateStr) ?? null;
            $collectionVal = ($payment && is_numeric($payment->collection)) ? (float) $payment->collection : 0.0;
            
            $isCollected = ($payment && $payment->is_collected == 1);
            if ($isCollected) {
                $runningPayment += $collectionVal;
            }

            $outstandingBalance = max(0, $totalLoanAmount - $runningPayment);

            $dueDate = \Carbon\Carbon::parse($dateStr)->startOfDay();
            $loanStart = \Carbon\Carbon::parse($loan->loan_from)->startOfDay();
            $days = $dueDate->lessThan($loanStart) ? 0 : $loanStart->diffInDays($dueDate, false) + 1;
            
            // If within term schedule (e.g. 100 days), compute expected balance; otherwise it is 0 (lapsed)
            if ($days <= $loanTerms && $dueDate->lte($termEndDate)) {
                $balanceShouldBe = max(0, $totalLoanAmount - ($days * $dailyRate));
            } else {
                $balanceShouldBe = 0;
            }

            $dailyOd = max(0, $outstandingBalance - $balanceShouldBe);

            // Pre-calculate reference number
            $refNo = $payment ? $payment->reference_number : null;
            if (!$refNo) {
                // Check if any other payment in the same area exists on this date
                $refNo = DB::table('clients_payments')
                    ->where('due_date', $dateStr)
                    ->where('client_area', $client->area_id)
                    ->whereNotNull('reference_number')
                    ->value('reference_number');

                if (!$refNo) {
                    $refNo = 'REF-' . $client->area_id . '-' . str_replace('-', '', $dateStr) . '-' . strtoupper(bin2hex(random_bytes(3)));
                }
            }

            $paymentsGrid[] = (object)[
                'index' => $index + 1,
                'date' => $dateStr,
                'payment_id' => $payment ? $payment->id : null,
                'collection' => $payment ? $payment->collection : null,
                'type' => $payment ? $payment->type : null,
                'savings_amount' => $payment ? $payment->savings_amount : null,
                'is_collected' => $isCollected ? 1 : 0,
                'balance_should_be' => $balanceShouldBe,
                'outstanding_balance' => $outstandingBalance,
                'total_payment' => $runningPayment,
                'daily_od' => $dailyOd,
                'reference_number' => $refNo
            ];
        }

        return view('admin.areas.manila.backlog_collections', compact(
            'loan',
            'client',
            'paymentsGrid'
        ));
    }

    public function AdminGetAreaNextPn($id)
    {
        $details = getAreaPnDetails($id);
        return response()->json($details);
    }

    public function AdminWeeklyCollectionReport(Request $request, $id)
    {
        $area = DB::table('areas')->where('id', $id)->first();
        if (!$area) {
            abort(404, 'Area not found.');
        }

        $areas_name = $area->areas_name ?? 'Unknown Area';
        $location_name = $area->location_name ?? 'Unknown Location';

        $matchedAreaIds = DB::table('areas')
            ->where('location_name', $location_name)
            ->where('areas_name', $areas_name)
            ->pluck('id')
            ->toArray();

        // Determine Date Range
        $fromInput = $request->query('from');
        $toInput = $request->query('to');
        $typeFilter = $request->query('type', 'all');

        if ($fromInput && $toInput) {
            $startDate = \Carbon\Carbon::parse($fromInput, 'Asia/Manila')->startOfDay();
            $endDate = \Carbon\Carbon::parse($toInput, 'Asia/Manila')->endOfDay();
        } else {
            $startDate = \Carbon\Carbon::now('Asia/Manila')->startOfWeek();
            $endDate = \Carbon\Carbon::now('Asia/Manila')->endOfWeek();
        }

        $fromFormatted = $startDate->format('Y-m-d');
        $toFormatted = $endDate->format('Y-m-d');

        // Dates that have payments/collections in this area within range
        $paymentDates = DB::table('clients_payments')
            ->whereIn('client_area', $matchedAreaIds)
            ->whereBetween('due_date', [$fromFormatted, $toFormatted])
            ->distinct()
            ->orderBy('due_date', 'asc')
            ->pluck('due_date')
            ->map(function ($d) {
                return \Carbon\Carbon::parse($d)->format('Y-m-d');
            })
            ->toArray();

        // If no payments recorded in date range, generate days (excluding Sunday)
        if (empty($paymentDates)) {
            $dates = [];
            $curr = $startDate->copy();
            while ($curr->lte($endDate)) {
                if (!$curr->isSunday()) {
                    $dates[] = $curr->format('Y-m-d');
                }
                $curr->addDay();
            }
        } else {
            $dates = $paymentDates;
        }

        // Clients belonging to this area
        $clients = DB::table('clients as c')
            ->whereIn('c.area_id', $matchedAreaIds)
            ->orderBy('c.fullname', 'asc')
            ->get();

        // Also check if other clients had payments under this area in this week
        $clientIdsFromPayments = DB::table('clients_payments')
            ->whereIn('client_area', $matchedAreaIds)
            ->whereBetween('due_date', [$fromFormatted, $toFormatted])
            ->pluck('client_id')
            ->toArray();

        $allClientIds = array_unique(array_merge(
            $clients->pluck('id')->toArray(),
            $clientIdsFromPayments
        ));

        $allClients = DB::table('clients')
            ->whereIn('id', $allClientIds)
            ->orderBy('fullname', 'asc')
            ->get();

        // Fetch latest active/current loans for these clients
        $loans = DB::table('clients_loans')
            ->whereIn('client_id', $allClientIds)
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('client_id');

        // Fetch payments for these clients in the date range
        $payments = DB::table('clients_payments')
            ->whereIn('client_id', $allClientIds)
            ->whereIn('client_area', $matchedAreaIds)
            ->whereBetween('due_date', [$fromFormatted, $toFormatted])
            ->get();

        // Index payments by client_id and due_date (Y-m-d)
        $paymentMap = [];
        $clientLapsedPayments = [];
        foreach ($payments as $p) {
            $dKey = \Carbon\Carbon::parse($p->due_date)->format('Y-m-d');
            $cId = $p->client_id;
            $col = (float)($p->collection ?? 0);

            if (!isset($paymentMap[$cId][$dKey])) {
                $paymentMap[$cId][$dKey] = 0;
            }
            if ($col > 0) {
                $paymentMap[$cId][$dKey] += $col;
            }
            if (!empty($p->is_lapsed)) {
                $clientLapsedPayments[$cId] = true;
            }
        }

        // Compute daily totals and client row data
        $dailyTotals = [];
        foreach ($dates as $d) {
            $dailyTotals[$d] = 0;
        }

        $reportRows = [];
        $compareDate = $endDate->copy()->startOfDay();

        foreach ($allClients as $c) {
            $clientPayments = [];
            $totalPaidThisWeek = 0;

            foreach ($dates as $d) {
                $colAmt = $paymentMap[$c->id][$d] ?? null;
                if ($colAmt !== null && $colAmt > 0) {
                    $clientPayments[$d] = $colAmt;
                    $totalPaidThisWeek += $colAmt;
                } else {
                    $clientPayments[$d] = null; // No payment
                }
            }

            $latestLoan = $loans[$c->id]->first() ?? null;
            $balance = (float)($latestLoan->balance ?? 0);
            $hasBalance = ($balance > 0);
            $loanEnd = !empty($latestLoan->loan_to) ? \Carbon\Carbon::parse($latestLoan->loan_to)->startOfDay() : null;

            // Determine if lapsed or active
            $isLapsed = ($hasBalance && $loanEnd && $compareDate->gt($loanEnd)) || (!empty($clientLapsedPayments[$c->id]) && $hasBalance);
            $isActive = (!$isLapsed) && ($hasBalance || $totalPaidThisWeek > 0);

            // Filter by type
            if ($typeFilter === 'active' && !$isActive) {
                continue;
            }
            if ($typeFilter === 'lapsed' && !$isLapsed) {
                continue;
            }

            // Sum into daily totals only for the filtered clients
            foreach ($dates as $d) {
                if (!empty($clientPayments[$d])) {
                    $dailyTotals[$d] += $clientPayments[$d];
                }
            }

            $displayName = $c->fullname;
            if ($latestLoan && !empty($latestLoan->pn_number) && !str_contains($c->fullname, '(')) {
                $displayName = $c->fullname . ' (' . $latestLoan->pn_number . ')';
            }

            $reportRows[] = (object)[
                'client_id' => $c->id,
                'fullname' => $displayName,
                'has_zero_payment' => ($totalPaidThisWeek <= 0),
                'payments' => $clientPayments,
                'total_paid' => $totalPaidThisWeek,
                'is_lapsed' => $isLapsed,
            ];
        }

        // Sort report rows alphabetically by fullname
        usort($reportRows, function ($a, $b) {
            return strcasecmp($a->fullname, $b->fullname);
        });

        $grandTotal = array_sum($dailyTotals);

        return view('admin.areas.print.weekly_collection_report', compact(
            'areas_name',
            'location_name',
            'id',
            'fromFormatted',
            'toFormatted',
            'dates',
            'reportRows',
            'dailyTotals',
            'grandTotal',
            'typeFilter'
        ));
    }
}
