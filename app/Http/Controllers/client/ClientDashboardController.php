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
            ->get();

        // Fetch recent payments of client
        $payments = DB::table('clients_payments as cp')
            ->leftJoin('collectors as col', 'cp.collected_by', '=', 'col.id')
            ->where('cp.client_id', $client->id)
            ->select('cp.*', 'col.fullname as collected_by_name')
            ->orderBy('cp.due_date', 'desc')
            ->orderBy('cp.created_at', 'desc')
            ->get();

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
