<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function LoginPage()
    {
        return view('auth.login');
    }

    public function LoginRequest(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        // ADMIN
        $admin = DB::table('admins')->where('email', $email)->first();
        if ($admin && Hash::check($password, $admin->password)) {
            Session::put('user', $admin);
            Session::put('role', 'admin');
            return redirect()->route('admin.dashboard.page')->with('success', 'Welcome back, Admin!');
        }

        // SECRETARY
        $secretary = DB::table('secretaries')->where('email', $email)->first();
        if ($secretary && Hash::check($password, $secretary->password)) {
            Session::put('user', $secretary);
            Session::put('role', 'secretary');
            return redirect()->route('secretary.dashboard.page')->with('success', 'Welcome back, Secretary!');
        }

        // COLLECTOR
        $collector = DB::table('collectors')->where('email', $email)->first();

        if ($collector && Hash::check($password, $collector->password)) {
            // Keep as object
            Session::put('user', $collector);
            Session::put('role', 'collector');

            $areas = DB::table('areas')->where('collector_id', $collector->id)->pluck('id')->toArray();
            Session::put('assigned_areas', $areas);

            return redirect()->route('collector.dashboard.page')
                ->with('success', 'Welcome back, Collector!');
        }

        return back()->with('error', 'Invalid credentials');
    }

    public function LogoutRequest()
    {
        Session::flush();
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
