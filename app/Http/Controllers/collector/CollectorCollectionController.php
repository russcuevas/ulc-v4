<?php

namespace App\Http\Controllers\collector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class CollectorCollectionController extends Controller
{
    /**
     * Show the collections page.
     */
    public function CollectorCollectionPage(Request $request)
    {
        $user = Session::get('user');
        $collectorId = $user->id;

        $selectedDate = $request->date ?? now()->format('Y-m-d');
        $today = \Carbon\Carbon::parse($selectedDate);

        // Get areas
        $areas = DB::table('areas')
            ->where('collector_id', $collectorId)
            ->get(['id', 'location_name', 'areas_name']);

        $area = $areas->first();
        $areaIds = $areas->pluck('id')->toArray();

        // ✅ FIXED QUERY
        // Include:
        // - balance > 0
        // - loan already started
        // - include overdue (removed loan_to filter)
        $loans = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->whereIn('c.area_id', $areaIds)
            ->whereDate('cl.loan_from', '<=', $selectedDate)
            ->select('cl.*', 'c.fullname', 'c.area_id')
            ->orderByDesc('cl.id')
            ->get();

        $payments = DB::table('clients_payments')
            ->whereDate('due_date', $selectedDate)
            ->whereIn('client_id', $loans->pluck('client_id')->toArray())
            ->get()
            ->keyBy(function ($item) {
                return (int)$item->client_id;
            });

        $clients = $loans->map(function ($loan) use ($payments, $selectedDate, $areas) {
            $area = $areas->firstWhere('id', $loan->area_id);
            $payment = $payments[(int)$loan->client_id] ?? null;

            $isOverdue = Carbon::parse($selectedDate)->gt(Carbon::parse($loan->loan_to));
            $isPaid = ($loan->balance ?? 0) <= 0; // Add this

            return (object)[
                'id' => $loan->client_id,
                'fullname' => $loan->fullname,
                'area_id' => $loan->area_id,
                'location_name' => $area->location_name ?? 'N/A',
                'loan' => $loan,
                'payment' => $payment,
                'is_overdue' => $isOverdue,
                'isPaid' => $isPaid // <-- include here
            ];
        })->filter(function ($c) {
            $balance = $c->loan->balance ?? 0;
            return $balance > 0 || ($c->payment && ($c->payment->collection ?? 0) > 0);
        })->values();

        $areas_name = $area->areas_name ?? 'UNKNOWN';
        $dateCode = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');
        $refNo = $dateCode . '-' . strtoupper($areas_name) . '-001';

        $totalClients = $clients->count();

        $totalCollections = DB::table('clients_payments')
            ->whereDate('due_date', $selectedDate)
            ->whereIn('client_id', $clients->pluck('id')->toArray())
            ->sum('collection');

        $totalDailyCollectibles = $clients->sum(function ($client) {
            return $client->loan->daily ?? 0;
        });

        return view('collector.collection.index', compact(
            'clients',
            'area',
            'refNo',
            'selectedDate',
            'totalClients',
            'totalCollections',
            'totalDailyCollectibles'
        ));
    }

    /**
     * Store a single client's collection.
     */
    public function CollectorCollectPaymentRequest(Request $request)
    {
        $user = Session::get('user');

        $loan = DB::table('clients_loans')
            ->where('id', $request->loan_id)
            ->first();

        if (!$loan) {
            return redirect()->back()->with('error', 'Loan not found.');
        }

        $request->validate([
            'collection' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($loan) {
                    if ($value !== null && $value > $loan->balance) {
                        $fail('Collection cannot exceed remaining balance.');
                    }
                }
            ],
            'type' => 'required|string'
        ]);

        $collectionAmount = $request->type === 'NO PAYMENT' ? null : $request->collection;

        DB::table('clients_payments')->insert([
            'reference_number' => $request->reference_no,
            'collected_by' => $user->id,
            'due_date' => $request->due_date,
            'client_id' => $request->client_id,
            'client_loans_id' => $request->loan_id,
            'client_area' => $request->area_id,
            'daily' => $request->daily,
            'old_balance' => $loan->balance, // ✅ real balance from DB
            'collection' => $collectionAmount,
            'type' => $request->type,
            'is_collected' => 0,
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Saved successfully!');
    }

    /**
     * Bulk store multiple client collections.
     */
    public function CollectorBulkCollectPaymentRequest(Request $request)
    {
        $user = Session::get('user');
        $payments = $request->payments;

        if (!$payments || !is_array($payments)) {
            return redirect()->back()->with('error', 'No collections selected.');
        }

        DB::beginTransaction();
        try {
            foreach ($payments as $paymentData) {
                if (empty($paymentData['type'])) continue;

                $loan = DB::table('clients_loans')
                    ->where('id', $paymentData['loan_id'])
                    ->first();

                if (!$loan) continue;

                $collectionAmount = $paymentData['type'] === 'NO PAYMENT' ? null : ($paymentData['collection'] ?? null);

                // Validation check for balance
                if ($collectionAmount !== null && $collectionAmount > $loan->balance) {
                    throw new \Exception("Collection for client ID " . $paymentData['client_id'] . " cannot exceed remaining balance.");
                }

                DB::table('clients_payments')->insert([
                    'reference_number' => $request->reference_no,
                    'collected_by' => $user->id,
                    'due_date' => $request->due_date,
                    'client_id' => $paymentData['client_id'],
                    'client_loans_id' => $paymentData['loan_id'],
                    'client_area' => $paymentData['area_id'],
                    'daily' => $paymentData['daily'],
                    'old_balance' => $loan->balance,
                    'collection' => $collectionAmount,
                    'type' => $paymentData['type'],
                    'is_collected' => 0,
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Bulk collections saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error during bulk save: ' . $e->getMessage());
        }
    }
}
