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

        $allAreas = DB::table('areas')
            ->select('areas.location_name', 'areas.areas_name', DB::raw('MIN(areas.id) as id'), DB::raw('MIN(areas.secretary_id) as secretary_id'))
            ->groupBy('areas.location_name', 'areas.areas_name')
            ->orderBy('location_name')
            ->orderBy('areas_name')
            ->get();

        foreach ($allAreas as $area) {
            $area->secretary_name = DB::table('secretaries')
                ->where('id', $area->secretary_id)
                ->value('fullname');
        }

        return view('admin.secretary.index', compact('secretaries', 'secretaryAreas', 'allAreas'));
    }

    public function AdminAddSecretary(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:secretaries,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string',
        ]);

        DB::table('secretaries')->insert([
            'fullname' => $request->fullname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'gender' => $request->gender,
            'status' => 'verified',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Secretary added successfully!');
    }

    public function AdminAssignSecretaryAreas(Request $request, $id)
    {
        $request->validate([
            'areas' => 'nullable|array',
            'areas.*' => 'exists:areas,id',
        ]);

        $assignedAreaIds = $request->input('areas', []);

        if (!empty($assignedAreaIds)) {
            $selectedAreas = DB::table('areas')
                ->whereIn('id', $assignedAreaIds)
                ->get();

            foreach ($selectedAreas as $selectedArea) {
                DB::table('areas')
                    ->where('location_name', $selectedArea->location_name)
                    ->where('areas_name', $selectedArea->areas_name)
                    ->update([
                        'secretary_id' => $id,
                        'updated_at' => now(),
                    ]);
            }
        }

        return redirect()->back()->with('success', 'Areas assigned successfully!');
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
