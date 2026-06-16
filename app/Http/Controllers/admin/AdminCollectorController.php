<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminCollectorController extends Controller
{
    public function AdminCollectorPage()
    {
        $collectors = DB::table('collectors')->get();

        $collectorAreas = DB::table('areas')
            ->join('collectors', 'collectors.id', '=', 'areas.collector_id')
            ->select(
                'areas.collector_id',
                'areas.location_name',
                'areas.areas_name',
                'collectors.fullname as collector_name'
            )
            ->get();

        return view('admin.collector.index', compact('collectors', 'collectorAreas'));
    }

    // Update collector name, email and password
    public function AdminUpdateCollector(Request $request, $id)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $updateData = [
            'fullname' => $request->fullname,
            'email' => $request->email,
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        DB::table('collectors')->where('id', $id)->update($updateData);

        return redirect()->back()->with('success', 'Collector updated successfully!');
    }
}
