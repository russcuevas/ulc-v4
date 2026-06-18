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
            ->select('areas.location_name', 'areas.areas_name', DB::raw('MIN(areas.id) as id'))
            ->groupBy('areas.location_name', 'areas.areas_name')
            ->orderBy('location_name')
            ->orderBy('areas_name')
            ->get()
            ->sortBy('areas_name', SORT_NATURAL);

        foreach ($allAreas as $area) {
            // Get all secretary IDs assigned to this area
            $secretaryIds = DB::table('areas')
                ->where('location_name', $area->location_name)
                ->where('areas_name', $area->areas_name)
                ->pluck('secretary_id')
                ->unique()
                ->toArray();

            // Get names of these secretaries
            $names = DB::table('secretaries')
                ->whereIn('id', $secretaryIds)
                ->pluck('fullname')
                ->toArray();

            $area->secretary_names = $names;
            $area->secretary_name = implode(', ', $names);
            $area->secretary_id = !empty($secretaryIds) ? $secretaryIds[0] : null;
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
            $selectedDistinctAreas = DB::table('areas')
                ->whereIn('id', $assignedAreaIds)
                ->select('location_name', 'areas_name')
                ->distinct()
                ->get();
        } else {
            $selectedDistinctAreas = collect();
        }

        // 1. Handle assignments (create duplicate rows for checked areas that aren't yet assigned to this secretary)
        foreach ($selectedDistinctAreas as $sel) {
            // Find all collectors/rows for this area that belong to ANY secretary
            $allCollectorRowsForArea = DB::table('areas')
                ->where('location_name', $sel->location_name)
                ->where('areas_name', $sel->areas_name)
                ->get();

            // Make sure this secretary has a row for each collector of this area
            foreach ($allCollectorRowsForArea as $row) {
                $exists = DB::table('areas')
                    ->where('secretary_id', $id)
                    ->where('collector_id', $row->collector_id)
                    ->where('location_name', $row->location_name)
                    ->where('areas_name', $row->areas_name)
                    ->exists();

                if (!$exists) {
                    DB::table('areas')->insert([
                        'secretary_id' => $id,
                        'collector_id' => $row->collector_id,
                        'location_name' => $row->location_name,
                        'areas_name' => $row->areas_name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 2. Handle unassignments (delete this secretary's rows for unchecked areas, as long as another secretary manages it)
        $currentSecretaryAreas = DB::table('areas')
            ->where('secretary_id', $id)
            ->select('location_name', 'areas_name')
            ->distinct()
            ->get();

        $skippedAreas = [];

        foreach ($currentSecretaryAreas as $curr) {
            $stillAssigned = $selectedDistinctAreas->contains(function ($sel) use ($curr) {
                return $sel->location_name === $curr->location_name && $sel->areas_name === $curr->areas_name;
            });

            if (!$stillAssigned) {
                // Check if this area is assigned to at least one OTHER secretary
                $otherSecretariesCount = DB::table('areas')
                    ->where('location_name', $curr->location_name)
                    ->where('areas_name', $curr->areas_name)
                    ->where('secretary_id', '!=', $id)
                    ->count();

                if ($otherSecretariesCount > 0) {
                    // Safe to delete this secretary's rows for this area
                    DB::table('areas')
                        ->where('secretary_id', $id)
                        ->where('location_name', $curr->location_name)
                        ->where('areas_name', $curr->areas_name)
                        ->delete();
                } else {
                    $skippedAreas[] = $curr->areas_name;
                }
            }
        }

        if (!empty($skippedAreas)) {
            $names = implode(', ', $skippedAreas);
            return redirect()->back()->with('success', 'Areas assigned successfully! Note: ' . $names . ' could not be unassigned because every area must have at least one secretary.');
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
