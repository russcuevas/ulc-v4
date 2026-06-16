<?php

namespace App\Http\Controllers\secretary\area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SecretaryCollectionController extends Controller
{

    public function SecretaryCollectionReferencesPage($areaId)
    {
        $secretary = Session::get('user');
        if (!$secretary) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $secretary_id = $secretary->id;

        // Get area
        $area = DB::table('areas')
            ->where('id', $areaId)
            ->where('secretary_id', $secretary_id)
            ->first();

        if (!$area) {
            return redirect()->route('secretary.areas.page')
                ->with('error', 'You are not authorized to access this area.');
        }

        $matchedAreaIds = DB::table('areas')
            ->where('location_name', $area->location_name)
            ->where('areas_name', $area->areas_name)
            ->pluck('id')
            ->toArray();

        // Get references
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

        // Count total clients per reference (filtered like SecretaryCollectionDetailPage)
        $references = $references->map(function ($ref) use ($matchedAreaIds) {

            // Get all loans started on or before due date
            $loans = DB::table('clients_loans as cl')
                ->join('clients as c', 'cl.client_id', '=', 'c.id')
                ->whereIn('c.area_id', $matchedAreaIds)
                ->whereDate('cl.loan_from', '<=', $ref->due_date)
                ->select('cl.*', 'c.id as client_id')
                ->get();

            // Get payments for this reference
            $payments = DB::table('clients_payments')
                ->whereIn('client_area', $matchedAreaIds)
                ->where('reference_number', $ref->reference_number)
                ->get()
                ->keyBy('client_loans_id');

            // Filter loans like in SecretaryCollectionDetailPage
            $filteredClients = $loans->filter(function ($loan) use ($payments) {
                $balance = $loan->balance ?? 0;
                $payment = $payments[$loan->id] ?? null;

                return $balance > 0 || ($balance <= 0 && $payment && ($payment->collection ?? 0) > 0);
            });

            $ref->total_clients = $filteredClients->count();

            // Total collections for this reference (sum of payment.collection for filtered clients)
            $ref->total_collections = $filteredClients->sum(function ($loan) use ($payments) {
                $payment = $payments[$loan->id] ?? null;
                return $payment ? ($payment->collection ?? 0) : 0;
            });

            // Total daily collectible for this reference (sum of loan.daily for filtered clients)
            $ref->total_daily_collectibles = $filteredClients->sum(function ($loan) {
                return $loan->daily ?? 0;
            });

            return $ref;
        });

        return view('secretary.areas.collections_references', [
            'references' => $references,
            'areaId' => $areaId,
            'location_name' => $area->location_name ?? 'N/A',
            'areas_name' => $area->areas_name ?? 'N/A'
        ]);
    }

    public function SecretaryCollectionDetailPage($referenceNumber)
    {
        $secretary = Session::get('user');
        if (!$secretary) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $secretary_id = $secretary->id;

        $reference = DB::table('clients_payments')
            ->where('reference_number', $referenceNumber)
            ->first();

        if (!$reference) {
            return redirect()->route('secretary.areas.page')
                ->with('error', 'Reference not found.');
        }

        $area = DB::table('areas')
            ->where('id', $reference->client_area)
            ->where('secretary_id', $secretary_id)
            ->first();

        if (!$area) {
            return redirect()->route('secretary.areas.page')
                ->with('error', 'Unauthorized.');
        }

        $selectedDate = $reference->due_date;

        $matchedAreaIds = DB::table('areas')
            ->where('location_name', $area->location_name)
            ->where('areas_name', $area->areas_name)
            ->pluck('id')
            ->toArray();

        // ✅ REMOVE balance filter
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

        // Payments
        $payments = DB::table('clients_payments')
            ->where('reference_number', $referenceNumber)
            ->get()
            ->keyBy('client_loans_id');

        // Combine
        // After you combine loans and payments
        $clients = $loans->map(function ($loan) use ($payments, $selectedDate) {

            $payment = $payments[$loan->id] ?? null;

            $isOverdue = \Carbon\Carbon::parse($selectedDate)
                ->gt(\Carbon\Carbon::parse($loan->loan_to));

            return (object)[
                'id' => $loan->client_id,
                'fullname' => $loan->fullname,
                'loan' => $loan,
                'payment' => $payment,
                'is_overdue' => $isOverdue
            ];
        });

        // ✅ Filter: hide fully paid loans that have no payment
        $clients = $clients->filter(function ($c) {
            $balance = $c->loan->balance ?? 0;

            // Show if:
            // 1. Balance > 0 (still owed)
            // 2. OR Balance = 0 **but there is a payment record**
            return $balance > 0 || ($balance <= 0 && $c->payment && ($c->payment->collection ?? 0) > 0);
        })->values(); // reset keys

        $totalClients = $clients->count();

        $totalCollections = $clients->sum(function ($c) {
            return $c->payment->collection ?? 0;
        });

        $totalDailyCollectibles = $clients->sum(function ($c) {
            return $c->loan->daily ?? 0;
        });

        return view('secretary.areas.collection_detail', [
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

    public function SecretaryCollectClientsPayment(Request $request, $refNo)
    {
        $secretary = Session::get('user');
        if (!$secretary) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $action = $request->input('action');

        // Get reference to find area and date
        $reference = DB::table('clients_payments')->where('reference_number', $refNo)->first();
        if (!$reference) {
            return response()->json(['message' => 'Reference not found'], 404);
        }

        $selectedDate = $reference->due_date;
        $areaId = $reference->client_area;

        $area = DB::table('areas')->where('id', $areaId)->first();
        $matchedAreaIds = DB::table('areas')
            ->where('location_name', $area->location_name ?? '')
            ->where('areas_name', $area->areas_name ?? '')
            ->pluck('id')
            ->toArray();

        // Get collector for this area
        $collector = $area->collector_id ?? null;

        $collectorName = DB::table('collectors')->where('id', $collector)->value('fullname') ?? 'Collector';

        // -----------------------------
        // Fetch loans depending on action
        // -----------------------------
        if ($action === 'no_payment') {
            $loans = DB::table('clients_loans as cl')
                ->join('clients as c', 'cl.client_id', '=', 'c.id')
                ->whereIn('c.area_id', $matchedAreaIds)
                ->where('cl.balance', '>', 0)
                ->select('cl.*', 'c.id as client_id')
                ->get();
        } else {
            $loans = DB::table('clients_loans as cl')
                ->join('clients as c', 'cl.client_id', '=', 'c.id')
                ->whereIn('c.area_id', $matchedAreaIds)
                ->where('cl.balance', '>', 0)
                ->whereDate('cl.loan_from', '<=', $selectedDate)
                ->select('cl.*', 'c.id as client_id')
                ->get();
        }

        // Fetch all existing payments
        $payments = DB::table('clients_payments')
            ->where('reference_number', $refNo)
            ->whereIn('client_loans_id', $loans->pluck('id'))
            ->get()
            ->keyBy('client_loans_id');

        $messagesToSend = [];

        foreach ($loans as $loan) {

            $payment = $payments[$loan->id] ?? null;

            // ✅ COMPUTE LAPSED STATUS
            $isLapsed = \Carbon\Carbon::parse($selectedDate)
                ->gt(\Carbon\Carbon::parse($loan->loan_to)) ? 1 : 0;

            // -----------------------------
            // Handle "No Payment"
            // -----------------------------
            if ($action === 'no_payment') {

                if (\Carbon\Carbon::parse($loan->loan_from)->lte($selectedDate)) {

                    if (!$payment) {

                        DB::table('clients_payments')->insert([
                            'reference_number' => (string) $refNo,
                            'client_id' => $loan->client_id,
                            'client_loans_id' => $loan->id,
                            'client_area' => $areaId,
                            'collection' => 0,
                            'type' => 'NO PAYMENT',
                            'is_lapsed' => $isLapsed,
                            'is_collected' => 1,
                            'due_date' => $selectedDate,
                            'daily' => $loan->daily ?? 0,
                            'old_balance' => $loan->balance ?? 0,
                            'created_by' => $secretary->id,
                            'collected_by' => $collector,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        // send SMS to client notifying NO PAYMENT
                        try {
                            $client = DB::table('clients')->where('id', $loan->client_id)->first();
                            $phone_number = $client->phone ?? null;
                            $clientName = $client->fullname ?? 'Kliyente';

                            $dueFormatted = \Carbon\Carbon::parse($selectedDate)->format('F d, Y');
                            $remaining = number_format($loan->balance ?? 0, 2);

                            $message = "{$clientName}, Wala po kaming natanggap ng bayad ngayong araw {$dueFormatted} bayad kay {$collectorName}, Ang iyong natitirang balanse ay ₱{$remaining} Maraming salamat!\n- Financing Corporation";

                            if ($phone_number) {
                                $messagesToSend[] = [
                                    'number' => $phone_number,
                                    'message' => strip_tags(str_replace("<br>", "\n", $message))
                                ];
                            }
                        } catch (\Exception $e) {
                            // Fail silently — do not block process on SMS failure
                        }
                    } else {

                        if ($payment->type === 'NO PAYMENT' || (($payment->collection === null || $payment->collection == 0) && $payment->type !== 'NO PAYMENT')) {

                            DB::table('clients_payments')
                                ->where('id', $payment->id)
                                ->update([
                                    'collection' => 0,
                                    'type' => 'NO PAYMENT',
                                    'is_lapsed' => $isLapsed,
                                    'is_collected' => 1,
                                    'updated_at' => now()
                                ]);
                            // send SMS to client notifying NO PAYMENT after update
                            try {
                                $client = DB::table('clients')->where('id', $loan->client_id)->first();
                                $phone_number = $client->phone ?? null;
                                $clientName = $client->fullname ?? 'Kliyente';

                                $dueFormatted = \Carbon\Carbon::parse($selectedDate)->format('F d, Y');
                                $remaining = number_format($loan->balance ?? 0, 2);

                                $message = "{$clientName}, Wala po kaming natanggap ng bayad ngayong araw {$dueFormatted} bayad kay {$collectorName}, Ang iyong natitirang balanse ay ₱{$remaining} Maraming salamat!\n- Financing Corporation";

                                if ($phone_number) {
                                    $messagesToSend[] = [
                                        'number' => $phone_number,
                                        'message' => strip_tags(str_replace("<br>", "\n", $message))
                                    ];
                                }
                            } catch (\Exception $e) {
                            }
                        }
                    }
                }
            }

            // REMINDER: add missing payment records (with null collection/type) and send SMS reminder
            if ($action === 'reminder') {
                if (\Carbon\Carbon::parse($loan->loan_from)->lte($selectedDate)) {
                    if (!$payment) {
                        DB::table('clients_payments')->insert([
                            'reference_number' => (string) $refNo,
                            'client_id' => $loan->client_id,
                            'client_loans_id' => $loan->id,
                            'client_area' => $areaId,
                            'collection' => null,
                            'type' => null,
                            'is_lapsed' => $isLapsed,
                            'is_collected' => 0,
                            'due_date' => $selectedDate,
                            'daily' => $loan->daily ?? 0,
                            'old_balance' => $loan->balance ?? 0,
                            'created_by' => $secretary->id,
                            'collected_by' => $collector,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        // send SMS reminder to client
                        /*
                        try {
                            $client = DB::table('clients')->where('id', $loan->client_id)->first();
                            $phone_number = $client->phone ?? null;
                            $clientName = $client->fullname ?? 'Kliyente';

                            $dailyAmount = number_format($loan->daily ?? 0, 2);
                            $dueFormatted = \Carbon\Carbon::parse($selectedDate)->format('F d, Y');
                            $remaining = number_format($loan->balance ?? 0, 2);

                            $message = "{$clientName}, paalala: wala pa pong bayad para sa {$dueFormatted}. Daily: ₱{$dailyAmount}. Bal: {$remaining}. Salamat.\n- Financing Corporation";

                            if ($phone_number) {
                                $messagesToSend[] = [
                                    'number' => $phone_number,
                                    'message' => strip_tags(str_replace("<br>", "\n", $message))
                                ];
                            }
                        } catch (\Exception $e) {
                            // Fail silently — reminder should not block the overall process
                        }
                        */
                    } else {
                        // Payment record already exists, meaning reminder was already sent.
                        // Do not send SMS again.
                    }
                }
            }

            // -----------------------------
            // Handle "Collect"
            // -----------------------------
            if ($action === 'collect') {

                if ($payment && $payment->collection > 0 && $payment->is_collected == 0 && $payment->type !== 'NO PAYMENT') {

                    $newBalance = $loan->balance - $payment->collection;
                    $newBalance = max($newBalance, 0);

                    DB::table('clients_payments')
                        ->where('id', $payment->id)
                        ->update([
                            'is_collected' => 1,
                            'is_lapsed' => $isLapsed,
                            'updated_at' => now()
                        ]);

                    DB::table('clients_loans')
                        ->where('id', $loan->id)
                        ->update([
                            'balance' => $newBalance,
                            'status' => $newBalance <= 0 ? 'paid' : 'unpaid',
                            'updated_at' => now()
                        ]);

                    // Send SMS notification to client about the collected payment (mirror admin behavior)
                    try {
                        $client = DB::table('clients')->where('id', $loan->client_id)->first();
                        $phone_number = $client->phone ?? null;
                        $clientName = $client->fullname ?? 'Kliyente';

                        $collectorId = $payment->collected_by ?? $collector;
                        $collectorName = DB::table('collectors')->where('id', $collectorId)->value('fullname') ?? 'Collector';

                        $dateCollected = \Carbon\Carbon::now()->format('M d Y');

                        $collectedAmount = number_format($payment->collection ?? 0, 2);
                        $remaining = number_format($newBalance, 2);
                        $paymentType = $payment->type ? strtoupper($payment->type) : 'PAYMENT';

                        $message = "{$clientName}, natanggap na ang bayad na ₱{$collectedAmount} ({$paymentType}) kay {$collectorName} noong {$dateCollected}. Bal: {$remaining}. Salamat.\n- Financing Corporation";


                        if ($phone_number) {
                            $messagesToSend[] = [
                                'number' => $phone_number,
                                'message' => strip_tags(str_replace("<br>", "\n", $message))
                            ];
                        }
                    } catch (\Exception $e) {
                        // Fail silently — collection should succeed even if SMS fails
                    }
                }
            }
        }

        // Send gathered messages in bulk
        if (!empty($messagesToSend) || $action === 'reminder') {
            try {
                // ==========================================
                // TESTING OVERRIDE:
                // Kung gusto mong i-disable ang pag-send sa tunay na mga client habang nag-tetest, i-uncomment ang linya sa ibaba para ma-clear ang bulk list:
                // $messagesToSend = [];
                // ==========================================

                $areaRecord = DB::table('areas')->where('id', $areaId)->first();
                $locationName = $areaRecord->location_name ?? '';
                $areasName = $areaRecord->areas_name ?? '';
                $approvedText = "approved text";
                if ($locationName || $areasName) {
                    $approvedText .= " " . implode(", ", array_filter([$locationName, $areasName]));
                }

                // Laging isasama ang mga test number na ito sa listahan na may "approved text" na mensahe:
                $messagesToSend[] = ['number' => '09338698564', 'message' => $approvedText];
                $messagesToSend[] = ['number' => '09380641945', 'message' => $approvedText];

                \sendMessages($messagesToSend);
            } catch (\Exception $e) {
                // Fail silently — do not block the transaction/action if SMS gateway fails
            }
        }


        if ($action === 'collect') {
            $msg = 'Payment collected successfully for all applicable clients.';
        } elseif ($action === 'reminder') {
            $msg = 'Reminders sent to all clients without payment.';
        } else {
            $msg = 'All clients without payment are now tagged as NO PAYMENT.';
        }

        return response()->json(['message' => $msg]);
    }

    public function SecretaryPrintCollection($refNo)
    {
        $secretary = Session::get('user');
        if (!$secretary) {
            return redirect('/login')->with('error', 'Please login first');
        }

        // Get reference
        $reference = DB::table('clients_payments')
            ->where('reference_number', $refNo)
            ->first();

        if (!$reference) {
            abort(404, 'Reference not found.');
        }

        // Validate area ownership
        $area = DB::table('areas')
            ->where('id', $reference->client_area)
            ->where('secretary_id', $secretary->id)
            ->first();

        if (!$area) {
            abort(403, 'Unauthorized.');
        }

        // Get full payment data
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
                'col.fullname as collected_by_name'
            )
            ->orderBy('c.fullname')
            ->get();

        if ($payments->isEmpty()) {
            abort(404, 'No payments found.');
        }

        return view('secretary.areas.print.print_collection', [
            'payments' => $payments,
            'area' => $area,
            'referenceNumber' => $refNo
        ]);
    }

    public function SecretaryPrintSummaryCollection(Request $request, $areaId)
    {
        $secretary = Session::get('user');
        if (!$secretary) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $secretary_id = $secretary->id;

        // Get area
        $area = DB::table('areas')
            ->where('id', $areaId)
            ->where('secretary_id', $secretary_id)
            ->first();

        if (!$area) {
            abort(403, 'Unauthorized or area not found.');
        }

        $allAreas = $request->query('all_areas') == 1;
        $from = $request->query('from');
        $to = $request->query('to');
        $filterAreaId = $request->query('filter_area_id', $areaId);

        if ($allAreas) {
            $areaIds = DB::table('areas')
                ->where('secretary_id', $secretary_id)
                ->pluck('id')
                ->toArray();

            $area = (object)[
                'areas_name' => 'All Areas',
                'location_name' => 'All Locations',
                'area_name' => 'All Areas'
            ];
        } else {
            $selectedArea = DB::table('areas')
                ->where('id', $filterAreaId)
                ->where('secretary_id', $secretary_id)
                ->first();

            if (!$selectedArea) {
                abort(403, 'Selected area unauthorized or not found.');
            }

            $areaIds = DB::table('areas')
                ->where('location_name', $selectedArea->location_name)
                ->where('areas_name', $selectedArea->areas_name)
                ->pluck('id')
                ->toArray();

            $area = $selectedArea;
            $area->area_name = $area->areas_name;
        }

        $references = DB::table('clients_payments as cp')
            ->select(
                'cp.reference_number',
                'cp.due_date',
                DB::raw('MAX(cp.collected_by) as collected_by'),
                DB::raw('COUNT(DISTINCT cp.client_id) as total_clients'),
                DB::raw('SUM(cp.daily) as total_daily_collectibles'),
                DB::raw('SUM(cp.collection) as total_collections')
            )
            ->whereIn('cp.client_area', $areaIds)
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('cp.due_date', [$from, $to]);
            })
            ->groupBy('cp.reference_number', 'cp.due_date')
            ->orderBy('cp.due_date', 'desc')
            ->get();

        // Count total clients per reference (filtered like SecretaryCollectionDetailPage)
        $references = $references->map(function ($ref) use ($areaIds) {
            $loans = DB::table('clients_loans as cl')
                ->join('clients as c', 'cl.client_id', '=', 'c.id')
                ->whereIn('c.area_id', $areaIds)
                ->whereDate('cl.loan_from', '<=', $ref->due_date)
                ->select('cl.*', 'c.id as client_id')
                ->get();

            $payments = DB::table('clients_payments')
                ->where('reference_number', $ref->reference_number)
                ->whereIn('client_loans_id', $loans->pluck('id'))
                ->get()
                ->keyBy('client_loans_id');

            $filteredClients = $loans->filter(function ($loan) use ($payments) {
                $balance = $loan->balance ?? 0;
                $payment = $payments[$loan->id] ?? null;

                return $balance > 0 || ($balance <= 0 && $payment && ($payment->collection ?? 0) > 0);
            });

            $ref->total_clients = $filteredClients->count();
            $ref->total_daily_collectibles = $filteredClients->sum(function ($loan) {
                return $loan->daily ?? 0;
            });
            $ref->total_collections = $filteredClients->sum(function ($loan) use ($payments) {
                $payment = $payments[$loan->id] ?? null;
                return $payment ? ($payment->collection ?? 0) : 0;
            });

            $collector = DB::table('collectors')->where('id', $ref->collected_by)->first();
            $ref->collected_by_name = $collector ? $collector->fullname : 'N/A';

            $ref->cash_count = DB::table('clients_payments')
                ->where('reference_number', $ref->reference_number)
                ->where('type', 'cash')
                ->count();

            $ref->advance_count = DB::table('clients_payments')
                ->where('reference_number', $ref->reference_number)
                ->where('type', 'advance')
                ->count();

            $ref->gcash_count = DB::table('clients_payments')
                ->where('reference_number', $ref->reference_number)
                ->where('type', 'gcash')
                ->count();

            $ref->cheque_count = DB::table('clients_payments')
                ->where('reference_number', $ref->reference_number)
                ->where('type', 'cheque')
                ->count();

            $ref->no_payment_count = DB::table('clients_payments')
                ->where('reference_number', $ref->reference_number)
                ->where('is_collected', 0)
                ->count();

            $ref->total_accounts = $ref->total_clients;
            $ref->active_amount = $ref->total_daily_collectibles;
            $ref->total_collection = $ref->total_collections;
            $ref->collected_by = $ref->collected_by_name;

            return $ref;
        });

        if (!$from || !$to) {
            $from = $references->min('due_date') ? $references->min('due_date') : now()->format('Y-m-d');
            $to = $references->max('due_date') ? $references->max('due_date') : now()->format('Y-m-d');
        }

        $location_name = $area->location_name;
        $areas_name = $area->areas_name;

        return view('secretary.areas.print.print_summary_collection', [
            'payments' => $references,
            'area' => $area,
            'from' => $from,
            'to' => $to,
            'location_name' => $location_name,
            'areas_name' => $areas_name
        ]);
    }

    public function SecretarySavePaymentCollection(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:clients_loans,id',
            'payment_id' => 'nullable|exists:clients_payments,id',
            'reference_number' => 'required',
            'due_date' => 'required|date',
            'client_area' => 'required',
            'collection' => 'required|numeric|min:0',
            'type' => 'required|string',
        ]);

        $loanId = $request->input('loan_id');
        $paymentId = $request->input('payment_id');
        $refNo = $request->input('reference_number');
        $dueDate = $request->input('due_date');
        $areaId = $request->input('client_area');
        $newCollection = (float) $request->input('collection');
        $type = strtoupper($request->input('type'));

        $loan = DB::table('clients_loans')->where('id', $loanId)->first();
        if (!$loan) {
            return response()->json(['message' => 'Loan not found.'], 404);
        }

        $secretary = Session::get('user');
        $collectorId = DB::table('areas')->where('id', $areaId)->value('collector_id');
        $isLapsed = \Carbon\Carbon::parse($dueDate)->gt(\Carbon\Carbon::parse($loan->loan_to)) ? 1 : 0;

        $payment = null;

        if (!empty($paymentId)) {
            $payment = DB::table('clients_payments')->where('id', $paymentId)->first();
        }

        if ($payment) {
            // Case A: Update existing payment
            $oldCollected = $payment->is_collected == 1 ? (float) ($payment->collection ?? 0) : 0.0;
            $newBalance = (float) $loan->balance + ($oldCollected - $newCollection);
            $newBalance = max($newBalance, 0);

            DB::table('clients_payments')
                ->where('id', $payment->id)
                ->update([
                    'collection' => $newCollection,
                    'type' => $type,
                    'is_collected' => 1,
                    'updated_at' => now(),
                ]);

            DB::table('clients_loans')
                ->where('id', $loan->id)
                ->update([
                    'balance' => $newBalance,
                    'status' => $newBalance <= 0 ? 'paid' : 'unpaid',
                    'updated_at' => now(),
                ]);

            $paymentRecordId = $payment->id;
        } else {
            // Case B: Create new payment
            $newBalance = (float) $loan->balance - $newCollection;
            $newBalance = max($newBalance, 0);

            $paymentRecordId = DB::table('clients_payments')->insertGetId([
                'reference_number' => (string) $refNo,
                'client_id' => $loan->client_id,
                'client_loans_id' => $loan->id,
                'client_area' => $areaId,
                'collection' => $newCollection,
                'type' => $type,
                'is_lapsed' => $isLapsed,
                'is_collected' => 1, // automatic collected
                'due_date' => $dueDate,
                'daily' => $loan->daily ?? 0,
                'old_balance' => $loan->balance ?? 0,
                'created_by' => $secretary->id ?? null,
                'collected_by' => $collectorId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('clients_loans')
                ->where('id', $loan->id)
                ->update([
                    'balance' => $newBalance,
                    'status' => $newBalance <= 0 ? 'paid' : 'unpaid',
                    'updated_at' => now(),
                ]);
        }

        // Create area notification
        try {
            $clientAreaId = DB::table('clients')->where('id', $loan->client_id)->value('area_id');
            DB::table('area_notifications')->insert([
                'area_id' => $clientAreaId,
                'type' => 'payment_collected',
                'data' => json_encode([
                    'loan_id' => $loan->id ?? null,
                    'client_id' => $loan->client_id ?? null,
                    'payment_id' => $paymentRecordId,
                    'amount' => $newCollection,
                    'message' => 'Payment received: ' . number_format($newCollection, 2),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Fail silently
        }

        return response()->json(['message' => 'Collection saved and updated successfully.']);
    }

    public function SecretaryReversePaymentCollection(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:clients_loans,id',
            'payment_id' => 'nullable|exists:clients_payments,id',
            'reference_number' => 'required',
            'due_date' => 'required|date',
            'client_area' => 'required',
        ]);

        $loanId = $request->input('loan_id');
        $paymentId = $request->input('payment_id');
        $refNo = $request->input('reference_number');
        $dueDate = $request->input('due_date');
        $areaId = $request->input('client_area');

        $loan = DB::table('clients_loans')->where('id', $loanId)->first();
        if (!$loan) {
            return response()->json(['message' => 'Loan not found.'], 404);
        }

        $secretary = Session::get('user');
        $collectorId = DB::table('areas')->where('id', $areaId)->value('collector_id');

        $payment = null;
        if (!empty($paymentId)) {
            $payment = DB::table('clients_payments')->where('id', $paymentId)->first();
        }

        if ($payment) {
            // Case A: Reverse existing payment record
            $oldCollected = $payment->is_collected == 1 ? (float) ($payment->collection ?? 0) : 0.0;
            $newBalance = (float) $loan->balance + $oldCollected;

            DB::table('clients_payments')
                ->where('id', $payment->id)
                ->update([
                    'collection' => null,
                    'type' => null,
                    'is_lapsed' => 0,
                    'is_collected' => 0,
                    'updated_at' => now(),
                ]);

            DB::table('clients_loans')
                ->where('id', $loan->id)
                ->update([
                    'balance' => $newBalance,
                    'status' => $newBalance <= 0 ? 'paid' : 'unpaid',
                    'updated_at' => now(),
                ]);
        } else {
            // Case B: Create new reversed/reset payment record
            DB::table('clients_payments')->insert([
                'reference_number' => (string) $refNo,
                'client_id' => $loan->client_id,
                'client_loans_id' => $loan->id,
                'client_area' => $areaId,
                'collection' => null,
                'type' => null,
                'is_lapsed' => 0,
                'is_collected' => 0,
                'due_date' => $dueDate,
                'daily' => $loan->daily ?? 0,
                'old_balance' => $loan->balance ?? 0,
                'created_by' => $secretary->id ?? null,
                'collected_by' => $collectorId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json(['message' => 'Collection reversed successfully.']);
    }
}
