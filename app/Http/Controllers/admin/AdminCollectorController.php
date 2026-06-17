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

        $uniqueAreas = DB::table('areas')
            ->select('location_name', 'areas_name')
            ->groupBy('location_name', 'areas_name')
            ->orderBy('location_name')
            ->orderBy('areas_name')
            ->get()
            ->sortBy('areas_name', SORT_NATURAL);

        // For each unique area, get the names of currently assigned collectors
        foreach ($uniqueAreas as $area) {
            $assignedCollectors = DB::table('areas')
                ->join('collectors', 'collectors.id', '=', 'areas.collector_id')
                ->where('areas.location_name', $area->location_name)
                ->where('areas.areas_name', $area->areas_name)
                ->select('areas.collector_id', 'collectors.fullname')
                ->get();
            
            $area->assigned_collectors = $assignedCollectors;
        }

        return view('admin.collector.index', compact('collectors', 'collectorAreas', 'uniqueAreas'));
    }

    public function AdminAssignCollectorAreas(Request $request, $id)
    {
        $request->validate([
            'areas' => 'nullable|array',
            'areas.*' => 'string',
        ]);

        $submittedAreas = $request->input('areas', []);

        // Parse submitted areas
        $submittedParsed = [];
        foreach ($submittedAreas as $submittedArea) {
            $parts = explode('|', $submittedArea);
            if (count($parts) === 2) {
                $submittedParsed[] = [
                    'location_name' => $parts[0],
                    'areas_name' => $parts[1]
                ];
            }
        }

        // Get currently assigned area rows for this collector
        $currentRows = DB::table('areas')
            ->where('collector_id', $id)
            ->get();

        // 1. Handle unassignments (items currently assigned but not in submitted list)
        foreach ($currentRows as $row) {
            $stillAssigned = false;
            foreach ($submittedParsed as $sub) {
                if ($sub['location_name'] === $row->location_name && $sub['areas_name'] === $row->areas_name) {
                    $stillAssigned = true;
                    break;
                }
            }

            if (!$stillAssigned) {
                // Check if this area has other collectors (so it doesn't disappear from areas table)
                $otherCollectorsCount = DB::table('areas')
                    ->where('location_name', $row->location_name)
                    ->where('areas_name', $row->areas_name)
                    ->where('id', '!=', $row->id)
                    ->count();

                if ($otherCollectorsCount > 0) {
                    // Find a remaining duplicate area row ID matching the location and name
                    $remainingArea = DB::table('areas')
                        ->where('location_name', $row->location_name)
                        ->where('areas_name', $row->areas_name)
                        ->where('id', '!=', $row->id)
                        ->first();

                    if ($remainingArea) {
                        // Transfer clients to the remaining area ID
                        DB::table('clients')
                            ->where('area_id', $row->id)
                            ->update(['area_id' => $remainingArea->id]);

                        // Transfer payments to the remaining area ID
                        DB::table('clients_payments')
                            ->where('client_area', $row->id)
                            ->update(['client_area' => $remainingArea->id]);
                    }

                    // Safe to delete
                    DB::table('areas')->where('id', $row->id)->delete();
                } else {
                    return redirect()->back()->with('error', "Cannot unassign area {$row->areas_name} from this collector because every area must have at least one collector. Please assign it to another collector first.");
                }
            }
        }

        // 2. Handle new assignments
        foreach ($submittedParsed as $sub) {
            $alreadyAssigned = $currentRows->contains(function ($row) use ($sub) {
                return $row->location_name === $sub['location_name'] && $row->areas_name === $sub['areas_name'];
            });

            if (!$alreadyAssigned) {
                // Get secretary_id of the existing area to copy it
                $existingArea = DB::table('areas')
                    ->where('location_name', $sub['location_name'])
                    ->where('areas_name', $sub['areas_name'])
                    ->first();

                $secretaryId = $existingArea ? $existingArea->secretary_id : 1; // fallback to 1

                DB::table('areas')->insert([
                    'secretary_id' => $secretaryId,
                    'collector_id' => $id,
                    'location_name' => $sub['location_name'],
                    'areas_name' => $sub['areas_name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Areas assigned to collector successfully!');
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

    public function AdminAddCollector(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:collectors,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string',
        ]);

        DB::table('collectors')->insert([
            'fullname' => $request->fullname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'gender' => $request->gender,
            'status' => 'verified',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Collector added successfully!');
    }
}
