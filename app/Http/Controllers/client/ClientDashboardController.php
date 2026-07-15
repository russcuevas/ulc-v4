<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ClientDashboardController extends Controller
{
    public function ClientDashboardPage()
    {
        $clientSession = Session::get('user');

        if (!$clientSession) {
            return redirect()->route('client.login.page')->with('error', 'Please login first.');
        }

        // Fetch fresh client data from database
        $client = DB::table('clients')->where('id', $clientSession->id)->first();

        if (!$client) {
            Session::flush();
            return redirect()->route('client.login.page')->with('error', 'Account not found.');
        }

        // Fetch area details
        $area = DB::table('areas')->where('id', $client->area_id)->first();

        // Fetch loans of client
        $loans = DB::table('clients_loans')
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($loan) {
                // Fetch sum of pending savings for this loan
                $pendingSavings = DB::table('clients_payments')
                    ->where('client_loans_id', $loan->id)
                    ->where('is_collected', 0)
                    ->sum('savings_amount') ?? 0;

                // Adjust savings_balance for the dashboard to exclude pending savings
                $loan->savings_balance = max(0, $loan->savings_balance - $pendingSavings);

                // If database balance is fully settled, mark display status as paid
                if ($loan->balance <= 0) {
                    $loan->status = 'paid';
                }

                // Set savings to 0 if the loan is not active (paid or settled)
                if ($loan->status === 'paid' || $loan->status === 'settled') {
                    $loan->savings_balance = 0;
                }

                return $loan;
            });

        // Fetch recent payments of client (only approved ones)
        $payments = DB::table('clients_payments as cp')
            ->leftJoin('collectors as col', 'cp.collected_by', '=', 'col.id')
            ->where('cp.client_id', $client->id)
            ->where('cp.is_collected', 1)
            ->select('cp.*', 'col.fullname as collected_by_name')
            ->orderBy('cp.due_date', 'desc')
            ->orderBy('cp.created_at', 'desc')
            ->get();

        // Calculate running balance dynamically to avoid database old_balance discrepancy
        // Group payments by loan ID
        $paymentsByLoan = [];
        foreach ($payments as $payment) {
            $paymentsByLoan[$payment->client_loans_id][] = $payment;
        }

        foreach ($paymentsByLoan as $loanId => $loanPayments) {
            // Find the corresponding loan
            $loan = $loans->firstWhere('id', $loanId);
            if (!$loan) {
                // Try fetching the loan directly if it is not in the client's current active loans list
                $loan = DB::table('clients_loans')->where('id', $loanId)->first();
            }

            if ($loan) {
                // Sort payments chronologically (oldest first) for running balance calculation
                usort($loanPayments, function ($a, $b) {
                    $dueDateCompare = strcmp($a->due_date, $b->due_date);
                    if ($dueDateCompare !== 0) {
                        return $dueDateCompare;
                    }
                    return strcmp($a->created_at, $b->created_at);
                });

                // Calculate the starting balance of the loan
                // starting_balance = current_loan_balance + sum(collection of all approved payments)
                $approvedSum = 0;
                foreach ($loanPayments as $payment) {
                    if ($payment->is_collected == 1) {
                        $approvedSum += ($payment->collection ?? 0.00);
                    }
                }
                
                $runningBalance = $loan->balance + $approvedSum;

                // Compute running balance for each payment (chronological order)
                foreach ($loanPayments as $payment) {
                    $payment->computed_old_balance = $runningBalance;
                    $payment->computed_remaining_balance = max(0, $runningBalance - ($payment->collection ?? 0.00));
                    $runningBalance = $payment->computed_remaining_balance;
                }
            }
        }

        return view('client.dashboard.index', compact('client', 'area', 'loans', 'payments'));
    }

    public function ClientChatPage()
    {
        $clientSession = Session::get('user');

        if (!$clientSession) {
            return redirect()->route('client.login.page')->with('error', 'Please login first.');
        }

        // Fetch fresh client data from database
        $client = DB::table('clients')->where('id', $clientSession->id)->first();

        if (!$client) {
            Session::flush();
            return redirect()->route('client.login.page')->with('error', 'Account not found.');
        }

        // Fetch area details
        $area = DB::table('areas')->where('id', $client->area_id)->first();

        return view('client.chat.index', compact('client', 'area'));
    }
}
