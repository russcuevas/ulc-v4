<?php

namespace App\Http\Controllers\secretary\area;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Collector;
use App\Models\Secretary;
use App\Notifications\NewClientNotification;

class SecretaryClientsController extends Controller
{
    public function SecretaryClientsPage($id)
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

        return view('secretary.areas.clients', compact('clients', 'areas_name', 'location_name', 'id', 'allAreas'));
    }

    public function SecretaryAddClientRequest(Request $request, $id)
    {
        // Validate input
        $request->validate([
            'fullname'       => 'required|string|max:255',
            'phone'          => 'required|digits:11',
            'phone_number_2' => 'nullable|digits:11',
            'area_id'        => "required|exists:areas,id|in:$id",
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
                'created_by' => 'Secretary', // ✅ changed
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
                'status'         => 'unpaid',
                'created_by'     => 'Secretary', // ✅ changed
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
    public function SecretaryViewClientLoans($id)
    {
        $client = DB::table('clients')
            ->where('id', $id)
            ->first();

        if (!$client) {
            return redirect()->back()->with('error', 'Client not found.');
        }

        // Get area
        $area = DB::table('areas')
            ->where('id', $client->area_id)
            ->first();

        $areas_name = $area->areas_name ?? 'Unknown Area';

        // ✅ Get location_name (assuming areas has location_name column)
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

        return view('secretary.areas.view_loans', compact(
            'areas_name',
            'location_name',
            'client',
            'loans',
            'allAreas'
        ));
    }

    public function SecretaryPrintSummaryLoan($clientId)
    {
        $client = DB::table('clients')
            ->where('id', $clientId)
            ->first();

        if (!$client) {
            return back()->with('error', 'Client not found.');
        }

        // Get area
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

        return view('secretary.areas.print.print_summary_loan', compact(
            'loans',
            'client',
            'area',
            'totalDaily',
            'totalAmount',
            'newCount',
            'renewalCount'
        ));
    }

    public function SecretaryUpdateClientRequest(Request $request, $id)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
            'phone_number_2' => 'nullable|string|max:20',
            'gender'   => 'required|string',
            'area_id'  => 'nullable|exists:areas,id',
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
                'phone'    => $request->phone,
                'phone_number_2' => $request->phone_number_2,
                'gender'   => $request->gender,
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

    public function SecretaryReassignClientArea(Request $request, $id)
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

    public function SecretarySubmitRenewLoan(Request $request, $clientId)
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
            'created_by'     => 'Secretary',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->back()->with('success', 'Loan renewed successfully.');
    }

    public function SecretaryGenerateSOA($loanId)
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

        return view('secretary.areas.print.generate_soa', compact(
            'loan',
            'client',
            'payments'
        ));
    }

    public function SecretaryBacklogCollections($loanId)
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

        // Generate date list from loan_from to loan_to
        $startDate = \Carbon\Carbon::parse($loan->loan_from);
        $endDate = \Carbon\Carbon::parse($loan->loan_to);

        $dateList = [];
        $tempDate = $startDate->copy();
        while ($tempDate->lte($endDate)) {
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

        foreach ($dateList as $index => $dateStr) {
            $payment = $payments->get($dateStr) ?? null;
            $collectionVal = ($payment && is_numeric($payment->collection)) ? (float) $payment->collection : 0.0;
            
            $isCollected = ($payment && $payment->is_collected == 1);
            if ($isCollected) {
                $runningPayment += $collectionVal;
            }

            $outstandingBalance = max(0, $loan->loan_amount - $runningPayment);

            $dueDate = \Carbon\Carbon::parse($dateStr);
            $loanStart = \Carbon\Carbon::parse($loan->loan_from);
            $days = $dueDate->lessThan($loanStart) ? 0 : $loanStart->diffInDays($dueDate, false) + 1;
            $balanceShouldBe = max(0, $loan->loan_amount - $days * ($loan->daily ?? 0));

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

        $paymentsGrid = array_slice($paymentsGrid, 0, 100);

        return view('secretary.areas.backlog_collections', compact(
            'loan',
            'client',
            'paymentsGrid'
        ));
    }

    public function SecretaryGetAreaNextPn($id)
    {
        $details = getAreaPnDetails($id);
        return response()->json($details);
    }
}
