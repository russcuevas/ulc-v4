<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSecretaryController extends Controller
{
    public function AdminSecretaryPage()
    {
        $secretaries = DB::table('secretaries')->get();

        $secretaryAreas = DB::table('areas')
            ->join('secretaries', 'secretaries.id', '=', 'areas.secretary_id')
            ->join('collectors', 'collectors.id', '=', 'areas.collector_id')
            ->select(
                'areas.secretary_id',
                'areas.location_name',
                'areas.areas_name',
                'collectors.fullname as collector_name'
            )
            ->get();

        return view('admin.secretary.index', compact('secretaries', 'secretaryAreas'));
    }

    public function AdminUpdateSecretary(Request $request, $id)
    {
        $updateData = [
            'fullname' => $request->fullname,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        DB::table('secretaries')
            ->where('id', $id)
            ->update($updateData);

        return redirect()->back()->with('success', 'Secretary updated successfully');
    }
}
