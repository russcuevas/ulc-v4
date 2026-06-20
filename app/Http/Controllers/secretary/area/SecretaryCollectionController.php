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
            ->first();

        if (!$area) {
            return redirect()->route('secretary.areas.page')
                ->with('error', 'Area not found.');
        }

        // Check if assigned to this secretary by location/name
        $isAssigned = DB::table('areas')
            ->where('location_name', $area->location_name)
            ->where('areas_name', $area->areas_name)
            ->where('secretary_id', $secretary_id)
            ->exists();

        if (!$isAssigned) {
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

    public function SecretaryWeeklyCollectionPage($location)
    {
        $secretary = Session::get('user');
        if (!$secretary) {
            return redirect('/login')->with('error', 'Please login first');
        }

        $secretary_id = $secretary->id;

        // Check if there are areas assigned to this secretary in this location
        $hasAssignedAreas = DB::table('areas')
            ->where('location_name', $location)
            ->where('secretary_id', $secretary_id)
            ->exists();

        if (!$hasAssignedAreas) {
            return redirect()->route('secretary.areas.page')
                ->with('error', 'You do not have assigned areas in this location.');
        }

        // Get all areas in this location assigned to this secretary
        $areas = DB::table('areas as a')
            ->leftJoin('collectors as col', 'a.collector_id', '=', 'col.id')
            ->where('a.secretary_id', $secretary_id)
            ->where('a.location_name', $location)
            ->select('a.location_name', 'a.areas_name', 'col.fullname as collector_name')
            ->orderBy('a.areas_name')
            ->get();

        // Group by areas_name to concatenate collectors on a single line
        $secretaryAreas = $areas->groupBy('areas_name')->map(function ($group) {
            $first = $group->first();
            $collectorNames = $group->pluck('collector_name')
                ->filter()
                ->unique()
                ->implode(', ');

            return (object) [
                'location_name' => $first->location_name,
                'areas_name' => $first->areas_name,
                'collector_name' => !empty($collectorNames) ? $collectorNames : 'Unassigned'
            ];
        })->values();

        return view('secretary.areas.weekly_collection', [
            'location_name' => $location,
            'secretaryAreas' => $secretaryAreas
        ]);
    }

    public function SecretaryWeeklyCollectClientsPayment(Request $request, $location)
    {
        $secretary = Session::get('user');
        if (!$secretary) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $secretary_id = $secretary->id;
        $dateInput = $request->input('date');

        if (!$dateInput) {
            return response()->json(['message' => 'Please select a starting date.'], 400);
        }

        $startDate = \Carbon\Carbon::parse($dateInput)->startOfDay();
        $endDate = $startDate->copy()->addDays(4)->endOfDay();

        // Check if weekly collection already exists in log
        $exists = DB::table('weekly_collections_log')
            ->where('location_name', $location)
            ->whereDate('start_date', $startDate->format('Y-m-d'))
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Weekly payment for this period has already been collected.'], 400);
        }

        // Get all areas in this location assigned to this secretary
        $myAreas = DB::table('areas')
            ->where('secretary_id', $secretary_id)
            ->where('location_name', $location)
            ->get();

        if ($myAreas->isEmpty()) {
            return response()->json(['message' => 'No areas assigned to you.'], 404);
        }

        $assignedAreaNames = $myAreas->pluck('areas_name')->unique()->toArray();

        // Get all areas sharing the same location and areas_name (even with different secretary IDs)
        $allMatchedAreas = DB::table('areas')
            ->where('location_name', $location)
            ->whereIn('areas_name', $assignedAreaNames)
            ->get();

        $areaIds = $allMatchedAreas->pluck('id')->toArray();

        // Get all active loans for these areas
        $loans = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->whereIn('c.area_id', $areaIds)
            ->where('cl.balance', '>', 0)
            ->select('cl.*', 'c.fullname', 'c.phone', 'c.id as client_id', 'c.area_id')
            ->get();

        if ($loans->isEmpty()) {
            return response()->json(['message' => 'No active loans found in your assigned areas.'], 404);
        }

        $messagesToSend = [];

        foreach ($loans as $loan) {
            $areaRecord = $allMatchedAreas->firstWhere('id', $loan->area_id);
            $collectorId = $areaRecord->collector_id ?? null;
            $collectorName = DB::table('collectors')->where('id', $collectorId)->value('fullname') ?? 'Collector';

            $paymentsList = [];
            $noPaymentDates = [];

            for ($i = 0; $i < 5; $i++) {
                $currentDay = $startDate->copy()->addDays($i)->format('Y-m-d');
                $currentDayObj = \Carbon\Carbon::parse($currentDay);
                $monthDay = $currentDayObj->format('M j');

                $isLapsed = $currentDayObj->gt(\Carbon\Carbon::parse($loan->loan_to)) ? 1 : 0;

                $payment = DB::table('clients_payments')
                    ->where('client_loans_id', $loan->id)
                    ->whereDate('due_date', $currentDay)
                    ->first();

                if ($payment) {
                    $collectionAmt = (float)($payment->collection ?? 0);
                    if ($payment->is_collected == 0) {
                        if ($collectionAmt > 0 && $payment->type !== 'NO PAYMENT') {
                            $newBalance = max(0, $loan->balance - $collectionAmt);

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

                            $loan->balance = $newBalance;
                            $paymentsList[] = "{$monthDay} - ₱" . number_format($collectionAmt, 0);
                        } else {
                            DB::table('clients_payments')
                                ->where('id', $payment->id)
                                ->update([
                                    'collection' => 0,
                                    'type' => 'NO PAYMENT',
                                    'is_lapsed' => $isLapsed,
                                    'is_collected' => 1,
                                    'updated_at' => now()
                                ]);

                            $noPaymentDates[] = $currentDay;
                        }
                    } else {
                        if ($collectionAmt > 0 && $payment->type !== 'NO PAYMENT') {
                            $paymentsList[] = "{$monthDay} - ₱" . number_format($collectionAmt, 0);
                        } else {
                            $noPaymentDates[] = $currentDay;
                        }
                    }
                } else {
                    $noPaymentDates[] = $currentDay;
                }
            }

            $noPaymentsByMonth = [];
            foreach ($noPaymentDates as $dateStr) {
                $dt = \Carbon\Carbon::parse($dateStr);
                $monthLabel = $dt->format('M');
                $dayNum = $dt->format('j');
                $noPaymentsByMonth[$monthLabel][] = $dayNum;
            }
            $noPaymentParts = [];
            foreach ($noPaymentsByMonth as $monthLabel => $days) {
                $noPaymentParts[] = "{$monthLabel} " . implode(',', $days);
            }
            $noPaymentText = implode(', ', $noPaymentParts);

            // Calculate latest outstanding balance and overdue at the end of the range
            $latestBalance = $loan->balance;
            $latestOverdue = 0;

            if ($latestBalance > 0) {
                $endDateObj = $endDate->copy()->startOfDay();
                $loanStart = \Carbon\Carbon::parse($loan->loan_from)->startOfDay();
                $days = $endDateObj->lessThan($loanStart) ? 0 : ($loanStart->diffInDays($endDateObj, false) + 1);
                $balanceShouldBe = max(0, ($loan->loan_amount ?? 0) - $days * ($loan->daily ?? 0));
                $latestOverdue = max(0, $latestBalance - $balanceShouldBe);
            }

            $phone_number = $loan->phone ?? null;
            $clientName = $loan->fullname ?? 'Kliyente';

            $greetings = [
                "Hello {$clientName},",
                "Hi {$clientName},",
                "Magandang araw, {$clientName}"
            ];
            $selectedGreeting = $greetings[array_rand($greetings)];

            $messageParts = [];
            $messageParts[] = $selectedGreeting;

            if (!empty($paymentsList)) {
                $messageParts[] = "Payments:\n" . implode("\n", $paymentsList);
            }

            if (!empty($noPaymentDates)) {
                $messageParts[] = "No payment: " . $noPaymentText;
            }

            $messageParts[] = "Outstanding: ₱" . number_format($latestBalance, 2) . "\nOverdue: ₱" . number_format($latestOverdue, 2);

            $messageParts[] = "natanggap ni {$collectorName} salamat";

            $message = implode("\n\n", $messageParts);

            if ($phone_number) {
                $messagesToSend[] = [
                    'number' => $phone_number,
                    'message' => strip_tags(str_replace("<br>", "\n", $message))
                ];
            }
        }

        if (!empty($messagesToSend)) {
            try {
                // $approvedText = "approved text";
                // array_unshift(
                //     $messagesToSend,
                //     ['number' => '09338698564', 'message' => $approvedText],
                //     ['number' => '09380641945', 'message' => $approvedText]
                // );

                \sendMessages($messagesToSend);
            } catch (\Exception $e) {
                // Fail silently
            }
        }

        // Log to weekly_collections_log
        $formattedRange = "FROM " . $startDate->format('F j, Y') . " TO " . $endDate->format('F j, Y');
        DB::table('weekly_collections_log')->insert([
            'location_name' => $location,
            'date_collected' => $formattedRange,
            'start_date' => $startDate->format('Y-m-d'),
            'is_sent' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'Weekly payment collected successfully and breakdown SMS sent to clients.'
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
            ->first();

        if (!$area) {
            return redirect()->route('secretary.areas.page')
                ->with('error', 'Area not found.');
        }

        $isAssigned = DB::table('areas')
            ->where('location_name', $area->location_name)
            ->where('areas_name', $area->areas_name)
            ->where('secretary_id', $secretary_id)
            ->exists();

        if (!$isAssigned) {
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

            // Outstanding balance (after today's payment if collected/entered)
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
                        }
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
                }
            }
        }

        if ($action === 'collect') {
            $msg = 'Payment collected successfully for all applicable clients.';
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
            ->first();

        if (!$area) {
            abort(404, 'Area not found.');
        }

        $isAssigned = DB::table('areas')
            ->where('location_name', $area->location_name)
            ->where('areas_name', $area->areas_name)
            ->where('secretary_id', $secretary->id)
            ->exists();

        if (!$isAssigned) {
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
            ->first();

        if (!$area) {
            abort(404, 'Area not found.');
        }

        $isAssigned = DB::table('areas')
            ->where('location_name', $area->location_name)
            ->where('areas_name', $area->areas_name)
            ->where('secretary_id', $secretary_id)
            ->exists();

        if (!$isAssigned) {
            abort(403, 'Unauthorized.');
        }

        $allAreas = $request->query('all_areas') == 1;
        $from = $request->query('from');
        $to = $request->query('to');
        $filterAreaId = $request->query('filter_area_id', $areaId);

        if ($allAreas) {
            $myUniqueAreas = DB::table('areas')
                ->where('secretary_id', $secretary_id)
                ->select('location_name', 'areas_name')
                ->distinct()
                ->get();

            $areaIds = DB::table('areas')
                ->where(function ($query) use ($myUniqueAreas) {
                    foreach ($myUniqueAreas as $ua) {
                        $query->orWhere(function ($q) use ($ua) {
                            $q->where('location_name', $ua->location_name)
                                ->where('areas_name', $ua->areas_name);
                        });
                    }
                })
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
                ->first();

            if (!$selectedArea) {
                abort(404, 'Selected area not found.');
            }

            $isSelectedAreaAssigned = DB::table('areas')
                ->where('location_name', $selectedArea->location_name)
                ->where('areas_name', $selectedArea->areas_name)
                ->where('secretary_id', $secretary_id)
                ->exists();

            if (!$isSelectedAreaAssigned) {
                abort(403, 'Selected area unauthorized.');
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

    public function SecretaryWeeklyCheckCollection(Request $request, $location)
    {
        $date = $request->query('date');
        if (!$date) {
            return response()->json(['exists' => false]);
        }

        $startDate = \Carbon\Carbon::parse($date)->startOfDay()->format('Y-m-d');

        $exists = DB::table('weekly_collections_log')
            ->where('location_name', $location)
            ->whereDate('start_date', $startDate)
            ->exists();

        return response()->json(['exists' => $exists]);
    }
}
