<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ClientAuthController extends Controller
{
    public function ClientLoginPage()
    {
        if (Session::has('role') && Session::get('role') === 'client') {
            return redirect()->route('client.dashboard.page');
        }
        return view('client.auth.index');
    }

    public function ClientLoginRequest(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = trim($request->phone);

        // Find client by phone or phone_number_2
        $client = DB::table('clients')
            ->where('phone', $phone)
            ->orWhere('phone_number_2', $phone)
            ->first();

        if (!$client) {
            return back()->with('error', 'No registered client found with that phone number.');
        }

        // Store client details and role in session
        Session::put('user', $client);
        Session::put('role', 'client');

        return redirect()->route('client.dashboard.page')->with('success', 'Welcome, ' . $client->fullname . '!');
    }

    public function ClientLogoutRequest()
    {
        Session::flush();
        return redirect()->route('client.login.page')->with('success', 'Logged out successfully.');
    }
}
