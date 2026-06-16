<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminChangePasswordController extends Controller
{
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $admin = Session::get('user');

        if (!$admin) {
            return redirect()->route('auth.login.page')->with('error', 'Please login first.');
        }

        // Get fresh admin details from database to check password
        $dbAdmin = DB::table('admins')->where('id', $admin->id)->first();

        if (!$dbAdmin || !Hash::check($request->current_password, $dbAdmin->password)) {
            return redirect()->back()->withErrors(['current_password' => 'The current password you entered is incorrect.']);
        }

        // Update password
        DB::table('admins')
            ->where('id', $admin->id)
            ->update([
                'password' => Hash::make($request->new_password),
                'updated_at' => now(),
            ]);

        // Update user password in the session as well
        $dbAdmin->password = Hash::make($request->new_password);
        Session::put('user', $dbAdmin);

        return redirect()->back()->with('success', 'Password changed successfully!');
    }
}
