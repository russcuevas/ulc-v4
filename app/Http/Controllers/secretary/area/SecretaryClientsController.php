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

        return view('secretary.areas.clients', compact('clients', 'areas_name', 'id'));
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

        return view('secretary.areas.view_loans', compact(
            'areas_name',
            'location_name',
            'client',
            'loans'
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
            $client->update([
                'fullname' => $request->fullname,
                'phone'    => $request->phone,
                'phone_number_2' => $request->phone_number_2,
                'gender'   => $request->gender,
            ]);

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
            ->orderBy('due_date', 'asc')
            ->get();

        return view('secretary.areas.print.generate_soa', compact(
            'loan',
            'client',
            'payments'
        ));
    }
}
