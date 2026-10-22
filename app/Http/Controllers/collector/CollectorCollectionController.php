<?php

namespace App\Http\Controllers\collector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class CollectorCollectionController extends Controller
{
    /**
     * Show the collections page.
     */
    public function CollectorCollectionPage(Request $request)
    {
        $user = Session::get('user');
        $collectorId = $user->id;

        $selectedDate = $request->date ?? now()->format('Y-m-d');
        $today = \Carbon\Carbon::parse($selectedDate);

        // Get areas assigned to this collector (grouped uniquely by location and area name)
        $myAreas = DB::table('areas')
            ->where('collector_id', $collectorId)
            ->select('location_name', 'areas_name', DB::raw('MIN(id) as id'))
            ->groupBy('location_name', 'areas_name')
            ->get()
            ->sortBy('areas_name', SORT_NATURAL);

        // Determine selected area
        $selectedAreaId = $request->area_id;
        if (empty($selectedAreaId)) {
            $selectedAreaId = $myAreas->first()?->id;
        }

        $area = $myAreas->firstWhere('id', $selectedAreaId);

        // Get all area IDs matching this collector's selected location and area name
        $allMatchedAreaIds = [];
        if ($area) {
            $allMatchedAreaIds = DB::table('areas')
                ->where('location_name', $area->location_name)
                ->where('areas_name', $area->areas_name)
                ->pluck('id')
                ->toArray();
        }

        $areas_name = $area->areas_name ?? 'UNKNOWN';
        $dateCode = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');
        $refNo = $dateCode . '-' . strtoupper($areas_name) . '-001';

        // Check for any loans that already have a payment tied to this reference number
        $paymentLoanIds = DB::table('clients_payments')
            ->where('reference_number', $refNo)
            ->pluck('client_loans_id')
            ->toArray();

        // ✅ QUERY: Include active loans for current area OR any loans specifically added to this reference number
        $loans = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->where(function ($q) use ($allMatchedAreaIds, $paymentLoanIds) {
                $q->whereIn('c.area_id', $allMatchedAreaIds)
                  ->orWhereIn('cl.id', $paymentLoanIds);
            })
            ->whereDate('cl.loan_from', '<=', $selectedDate)
            ->select('cl.*', 'c.fullname', 'c.area_id')
            ->orderByDesc('cl.id')
            ->get();

        // Get matched area records keyed by ID for display lookup (including any imported client areas)
        $allReferencedAreaIds = array_unique(array_merge($allMatchedAreaIds, $loans->pluck('area_id')->toArray()));
        $matchedAreas = DB::table('areas')
            ->whereIn('id', $allReferencedAreaIds)
            ->get()
            ->keyBy('id');

        $payments = DB::table('clients_payments')
            ->where('reference_number', $refNo)
            ->get()
            ->keyBy(function ($item) {
                return (int)$item->client_loans_id;
            });

        $clients = $loans->map(function ($loan) use ($payments, $selectedDate, $matchedAreas) {
            $areaRecord = $matchedAreas[$loan->area_id] ?? null;
            $payment = $payments[(int)$loan->id] ?? null;

            $isOverdue = Carbon::parse($selectedDate)->gt(Carbon::parse($loan->loan_to));
            $isPaid = ($loan->balance ?? 0) <= 0 || ($loan->status ?? '') === 'paid';

            return (object)[
                'id' => $loan->client_id,
                'fullname' => $loan->fullname,
                'area_id' => $loan->area_id,
                'location_name' => $areaRecord->location_name ?? 'N/A',
                'area_name' => $areaRecord->areas_name ?? '',
                'loan' => $loan,
                'payment' => $payment,
                'is_overdue' => $isOverdue,
                'isPaid' => $isPaid
            ];
        })->filter(function ($c) {
            $balance = $c->loan->balance ?? 0;
            $status = $c->loan->status ?? '';
            return ($balance > 0 && $status !== 'paid') 
                || ($c->payment && ($c->payment->collection ?? 0) > 0)
                || ($c->payment && $c->payment->type !== null);
        })->values();

        $totalClients = $clients->count();

        $totalCollections = $clients->sum(function ($c) {
            return $c->payment->collection ?? 0;
        });

        $totalDailyCollectibles = $clients->sum(function ($client) {
            return $client->loan->daily ?? 0;
        });

        $totalSavings = $clients->sum(function ($c) {
            return $c->payment->savings_amount ?? 0;
        });

        // Strict mapping: FC1 => FC-SA1, FC2 => FC-SA2, FC3 => FC-SA3, FC4 => FC-SA4, FC7 => FC-SA7
        $areaSaMap = [
            'FC1' => 'FC-SA1',
            'FC2' => 'FC-SA2',
            'FC3' => 'FC-SA3',
            'FC4' => 'FC-SA4',
            'FC7' => 'FC-SA7',
        ];

        $currentAreaName = strtoupper(trim($area->areas_name ?? ''));
        $mappedSaName = $areaSaMap[$currentAreaName] ?? null;
        $mappedSaArea = null;
        $hasSaMapping = false;

        if ($mappedSaName) {
            $mappedSaArea = DB::table('areas')
                ->where('areas_name', $mappedSaName)
                ->first();
            if ($mappedSaArea) {
                $hasSaMapping = true;
            }
        }

        return view('collector.collection.index', compact(
            'clients',
            'area',
            'refNo',
            'selectedDate',
            'totalClients',
            'totalCollections',
            'totalDailyCollectibles',
            'totalSavings',
            'myAreas',
            'selectedAreaId',
            'hasSaMapping',
            'mappedSaArea',
            'mappedSaName'
        ));
    }

    /**
     * Store a single client's collection.
     */
    public function CollectorCollectPaymentRequest(Request $request)
    {
        $user = Session::get('user');

        $loan = DB::table('clients_loans')
            ->where('id', $request->loan_id)
            ->first();

        if (!$loan) {
            return redirect()->back()->with('error', 'Loan not found.');
        }

        $rules = [
            'type' => 'required|string',
            'remarks' => 'nullable|string',
        ];

        if ($request->type === 'NO PAYMENT') {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240';
            $rules['collection'] = 'nullable';
            $rules['savings'] = 'nullable';
        } else {
            $rules['collection'] = [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($loan) {
                    if ($value !== null && $value > $loan->balance) {
                        $fail('Collection cannot exceed remaining balance.');
                    }
                }
            ];
            $rules['savings'] = 'nullable|numeric|min:0';
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240';
        }

        $messages = [
            'image.required' => 'Proof image is required when tagging as NO PAYMENT.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, webp, gif.',
            'image.max' => 'The image size may not be greater than 10MB.',
        ];

        $request->validate($rules, $messages);

        $collectionAmount = $request->type === 'NO PAYMENT' ? 0.00 : $request->collection;
        $savingsAmount = ($request->type === 'NO PAYMENT' || empty($request->savings)) ? 0.00 : $request->savings;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension());
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            $filename = time() . '_' . uniqid() . '.' . $ext;
            $destinationPath = public_path('collections/images');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $targetFullPath = $destinationPath . DIRECTORY_SEPARATOR . $filename;
            $savedFilename = $this->saveOptimizedImage($file, $targetFullPath, 1600, 75);
            $imagePath = 'collections/images/' . $savedFilename;
        }

        DB::transaction(function () use ($request, $user, $loan, $collectionAmount, $savingsAmount, $imagePath) {
            $existingPayment = DB::table('clients_payments')
                ->where('client_loans_id', $request->loan_id)
                ->whereDate('due_date', $request->due_date)
                ->first();

            $paymentData = [
                'reference_number' => $request->reference_no,
                'collected_by' => $user->id ?? 1,
                'due_date' => $request->due_date,
                'client_id' => $request->client_id,
                'client_loans_id' => $request->loan_id,
                'client_area' => $request->area_id,
                'daily' => $request->daily,
                'old_balance' => $loan->balance, // real balance from DB
                'collection' => $collectionAmount,
                'savings_amount' => $savingsAmount,
                'type' => $request->type,
                'remarks' => $request->remarks,
                'is_collected' => 0,
                'created_by' => $user->id ?? 1,
                'updated_at' => now(),
            ];

            if ($imagePath !== null) {
                $paymentData['image'] = $imagePath;
            }

            if ($existingPayment) {
                DB::table('clients_payments')
                    ->where('id', $existingPayment->id)
                    ->update($paymentData);
            } else {
                $paymentData['created_at'] = now();
                DB::table('clients_payments')->insert($paymentData);
            }

            if ($request->type !== 'NO PAYMENT' && $savingsAmount > 0) {
                DB::table('clients_loans')
                    ->where('id', $request->loan_id)
                    ->increment('savings_balance', $savingsAmount);
            }
        });

        return redirect()->back()->with('success', 'Saved successfully!');
    }

    /**
     * Bulk store multiple client collections.
     */
    public function CollectorBulkCollectPaymentRequest(Request $request)
    {
        $user = Session::get('user');
        $payments = $request->payments;

        if (!$payments || !is_array($payments)) {
            return redirect()->back()->with('error', 'No collections selected.');
        }

        DB::beginTransaction();
        try {
            foreach ($payments as $paymentData) {
                if (empty($paymentData['type'])) continue;
                if ($paymentData['type'] === 'NO PAYMENT') {
                    // Skip NO PAYMENT in bulk save because it requires proof image upload via single save
                    continue;
                }

                $loan = DB::table('clients_loans')
                    ->where('id', $paymentData['loan_id'])
                    ->first();

                if (!$loan) continue;

                $collectionAmount = $paymentData['type'] === 'NO PAYMENT' ? null : ($paymentData['collection'] ?? null);

                // Validation check for balance
                if ($collectionAmount !== null && $collectionAmount > $loan->balance) {
                    throw new \Exception("Collection for client ID " . $paymentData['client_id'] . " cannot exceed remaining balance.");
                }

                DB::table('clients_payments')->insert([
                    'reference_number' => $request->reference_no,
                    'collected_by' => $user->id,
                    'due_date' => $request->due_date,
                    'client_id' => $paymentData['client_id'],
                    'client_loans_id' => $paymentData['loan_id'],
                    'client_area' => $paymentData['area_id'],
                    'daily' => $paymentData['daily'],
                    'old_balance' => $loan->balance,
                    'collection' => $collectionAmount,
                    'savings_amount' => $paymentData['savings'] ?? 0.00,
                    'type' => $paymentData['type'],
                    'is_collected' => 0,
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Increment savings balance if savings is input
                $savingsAmount = $paymentData['savings'] ?? null;
                if ($savingsAmount !== null && is_numeric($savingsAmount) && $savingsAmount > 0) {
                    DB::table('clients_loans')
                        ->where('id', $paymentData['loan_id'])
                        ->increment('savings_balance', $savingsAmount);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Bulk collections saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error during bulk save: ' . $e->getMessage());
        }
    }

    /**
     * Compress and optimize uploaded proof image to keep file size well under 1MB.
     */
    protected function saveOptimizedImage($file, $destinationFullPath, $maxDimension = 1600, $quality = 75)
    {
        $realPath = $file->getRealPath();
        $finalFilename = basename($destinationFullPath);
        $dir = dirname($destinationFullPath);

        if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
            $imageData = @file_get_contents($realPath);
            if ($imageData !== false) {
                $srcImg = @imagecreatefromstring($imageData);
                if ($srcImg !== false) {
                    $width = imagesx($srcImg);
                    $height = imagesy($srcImg);

                    // Resize if larger than maxDimension
                    if ($width > $maxDimension || $height > $maxDimension) {
                        if ($width > $height) {
                            $newWidth = $maxDimension;
                            $newHeight = (int)round(($height * $maxDimension) / $width);
                        } else {
                            $newHeight = $maxDimension;
                            $newWidth = (int)round(($width * $maxDimension) / $height);
                        }

                        $dstImg = imagecreatetruecolor($newWidth, $newHeight);
                        $white = imagecolorallocate($dstImg, 255, 255, 255);
                        imagefilledrectangle($dstImg, 0, 0, $newWidth, $newHeight, $white);

                        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        imagedestroy($srcImg);
                        $srcImg = $dstImg;
                    }

                    $ext = strtolower($file->getClientOriginalExtension());
                    if ($ext === 'png' && $file->getSize() > 800 * 1024) {
                        $finalFilename = preg_replace('/\.png$/i', '.jpg', $finalFilename);
                        $destinationFullPath = $dir . DIRECTORY_SEPARATOR . $finalFilename;
                        imagejpeg($srcImg, $destinationFullPath, $quality);
                    } elseif ($ext === 'png') {
                        imagepng($srcImg, $destinationFullPath, 6);
                    } elseif ($ext === 'webp' && function_exists('imagewebp')) {
                        imagewebp($srcImg, $destinationFullPath, $quality);
                    } else {
                        imagejpeg($srcImg, $destinationFullPath, $quality);
                    }

                    imagedestroy($srcImg);
                    return $finalFilename;
                }
            }
        }

        // Fallback: move file directly
        $file->move($dir, $finalFilename);
        return $finalFilename;
    }

    /**
     * Get clients of a selected Special Area (e.g. FC-SA1) along with their payment status on the selected date.
     */
    public function CollectorGetSaClients(Request $request)
    {
        $saAreaId = $request->input('sa_area_id');
        $targetRefNo = $request->input('target_ref_no');
        $selectedDate = $request->input('selected_date', now()->format('Y-m-d'));

        $saArea = DB::table('areas')->where('id', $saAreaId)->first();
        if (!$saArea) {
            return response()->json([
                'success' => false,
                'message' => 'Special Area not found.',
                'clients' => []
            ], 404);
        }

        // Strict mapping check: FC1 = FC-SA1, FC2 = FC-SA2, FC3 = FC-SA3, FC4 = FC-SA4, FC7 = FC-SA7
        $areaSaMap = [
            'FC1' => 'FC-SA1',
            'FC2' => 'FC-SA2',
            'FC3' => 'FC-SA3',
            'FC4' => 'FC-SA4',
            'FC7' => 'FC-SA7',
        ];

        if (!empty($targetRefNo)) {
            $parts = explode('-', $targetRefNo);
            if (count($parts) >= 4) {
                $currentAreaCode = strtoupper($parts[3]);
                $allowedSaName = $areaSaMap[$currentAreaCode] ?? null;
                if (!$allowedSaName || strtoupper($saArea->areas_name) !== strtoupper($allowedSaName)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Access restricted. {$currentAreaCode} can only access {$allowedSaName}.",
                        'clients' => []
                    ], 403);
                }
            }
        }

        // Get loans for this SA area
        $loans = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->where('c.area_id', $saAreaId)
            ->whereDate('cl.loan_from', '<=', $selectedDate)
            ->select('cl.*', 'c.fullname', 'c.id as client_id', 'c.area_id')
            ->orderBy('c.fullname', 'asc')
            ->get();

        $loanIds = $loans->pluck('id')->toArray();

        // Get payments on the selected date (or tied to target ref)
        $payments = DB::table('clients_payments')
            ->where(function ($q) use ($selectedDate, $targetRefNo) {
                $q->whereDate('due_date', $selectedDate)
                  ->orWhere('reference_number', $targetRefNo);
            })
            ->whereIn('client_loans_id', $loanIds)
            ->get()
            ->sortByDesc(function ($p) use ($targetRefNo) {
                return $p->reference_number === $targetRefNo ? 1 : 0;
            })
            ->keyBy('client_loans_id');

        $clients = $loans->map(function ($loan) use ($payments, $targetRefNo) {
            $payment = $payments->get($loan->id);

            $isAlreadyAdded = ($payment && $payment->reference_number === $targetRefNo);

            $hasPayment = false;
            $isNoPayment = false;
            $paymentType = null;
            $collectionAmount = 0.00;
            $savingsAmount = 0.00;

            if ($payment) {
                $paymentType = $payment->type;
                $collectionAmount = (float)($payment->collection ?? 0);
                $savingsAmount = (float)($payment->savings_amount ?? 0);
                $isNoPayment = ($payment->type === 'NO PAYMENT' || ($payment->collection == 0 && $payment->type !== null));
                $hasPayment = ($collectionAmount > 0);
            }

            if ($isAlreadyAdded) {
                $statusLabel = 'Added to Table';
                $statusClass = 'badge-success';
                if ($isNoPayment) {
                    $statusLabel .= ' (NO PAYMENT)';
                } elseif ($hasPayment) {
                    $statusLabel .= ' (₱' . number_format($collectionAmount, 2) . ' ' . $paymentType . ')';
                }
            } elseif ($hasPayment) {
                $statusLabel = '₱' . number_format($collectionAmount, 2) . ' (' . $paymentType . ')';
                $statusClass = 'badge-success';
            } elseif ($isNoPayment) {
                $statusLabel = 'NO PAYMENT';
                $statusClass = 'badge-danger';
            } else {
                $statusLabel = 'Pending / No Record';
                $statusClass = 'badge-secondary';
            }

            return [
                'client_id' => $loan->client_id,
                'loan_id' => $loan->id,
                'fullname' => $loan->fullname,
                'daily' => (float)$loan->daily,
                'balance' => (float)$loan->balance,
                'is_already_added' => $isAlreadyAdded,
                'has_payment' => $hasPayment,
                'is_no_payment' => $isNoPayment,
                'payment_type' => $paymentType,
                'collection_amount' => $collectionAmount,
                'savings_amount' => $savingsAmount,
                'status_label' => $statusLabel,
                'status_class' => $statusClass,
                'payment_id' => $payment?->id,
                'remarks' => $payment?->remarks ?? '',
                'image' => !empty($payment?->image) ? asset($payment->image) : '',
            ];
        })->filter(function ($item) {
            return ($item['balance'] > 0) || $item['has_payment'] || $item['is_no_payment'] || $item['is_already_added'];
        })->values();

        return response()->json([
            'success' => true,
            'clients' => $clients,
            'total' => $clients->count(),
            'sa_area_name' => $saArea->areas_name
        ]);
    }

    /**
     * Add specific client(s) from Special Area into the current collection sheet.
     */
    public function CollectorAddSpecificClient(Request $request)
    {
        $user = Session::get('user');
        $collectorId = $user->id ?? 1;

        $loanIds = (array)$request->input('loan_ids', []);
        if (empty($loanIds) && $request->filled('loan_id')) {
            $loanIds = [$request->input('loan_id')];
        }

        if (empty($loanIds)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No client selected.'], 422);
            }
            return redirect()->back()->with('error', 'No client selected.');
        }

        $targetRefNo = $request->input('reference_no');
        $dueDate = $request->input('due_date');
        $targetAreaId = $request->input('area_id');

        $targetArea = DB::table('areas')->where('id', $targetAreaId)->first();
        $targetAreaName = strtoupper(trim($targetArea?->areas_name ?? ''));

        // Strict mapping check: FC1 = FC-SA1, FC2 = FC-SA2, FC3 = FC-SA3, FC4 = FC-SA4, FC7 = FC-SA7
        $areaSaMap = [
            'FC1' => 'FC-SA1',
            'FC2' => 'FC-SA2',
            'FC3' => 'FC-SA3',
            'FC4' => 'FC-SA4',
            'FC7' => 'FC-SA7',
        ];

        $allowedSaName = $areaSaMap[$targetAreaName] ?? null;
        if (!$allowedSaName) {
            $msg = "Area {$targetAreaName} does not have access to import from Special Area.";
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return redirect()->back()->with('error', $msg);
        }

        $allowedSaArea = DB::table('areas')->where('areas_name', $allowedSaName)->first();
        $allowedSaAreaId = $allowedSaArea?->id;

        $addedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($loanIds as $loanId) {
                $loan = DB::table('clients_loans as cl')
                    ->join('clients as c', 'cl.client_id', '=', 'c.id')
                    ->where('cl.id', $loanId)
                    ->select('cl.*', 'c.area_id as client_home_area')
                    ->first();
                if (!$loan) continue;

                // Strict check: only allow loans belonging to the mapped SA area
                if ($allowedSaAreaId && $loan->client_home_area != $allowedSaAreaId) {
                    continue;
                }

                // Check if a payment already exists on this due_date for this loan
                $existingPayment = DB::table('clients_payments')
                    ->where('client_loans_id', $loanId)
                    ->whereDate('due_date', $dueDate)
                    ->first();

                if ($existingPayment) {
                    // Update reference_number and client_area to bring into current sheet
                    DB::table('clients_payments')
                        ->where('id', $existingPayment->id)
                        ->update([
                            'reference_number' => $targetRefNo,
                            'client_area' => $targetAreaId,
                            'updated_at' => now(),
                        ]);
                    $addedCount++;
                } else {
                    // Insert a placeholder payment record so client appears in the collection table
                    DB::table('clients_payments')->insert([
                        'reference_number' => $targetRefNo,
                        'collected_by' => $collectorId,
                        'due_date' => $dueDate,
                        'client_id' => $loan->client_id,
                        'client_loans_id' => $loan->id,
                        'client_area' => $targetAreaId,
                        'daily' => $loan->daily,
                        'old_balance' => $loan->balance,
                        'collection' => null,
                        'savings_amount' => 0.00,
                        'type' => null,
                        'is_lapsed' => Carbon::parse($dueDate)->gt(Carbon::parse($loan->loan_to)) ? 1 : 0,
                        'is_collected' => 0,
                        'created_by' => $collectorId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $addedCount++;
                }
            }

            DB::commit();

            $msg = "Successfully added {$addedCount} client(s) to collection sheet!";
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove an imported client from the current collection sheet.
     */
    public function CollectorRemoveSpecificClient(Request $request)
    {
        $loanId = $request->input('loan_id');
        $paymentId = $request->input('payment_id');

        $payment = null;
        if ($paymentId) {
            $payment = DB::table('clients_payments')->where('id', $paymentId)->first();
        } elseif ($loanId && $request->filled('due_date')) {
            $payment = DB::table('clients_payments')
                ->where('client_loans_id', $loanId)
                ->whereDate('due_date', $request->input('due_date'))
                ->first();
        }

        if (!$payment) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Payment record not found.'], 404);
            }
            return redirect()->back()->with('error', 'Payment record not found.');
        }

        // Get the client's home area
        $client = DB::table('clients')->where('id', $payment->client_id)->first();
        $homeArea = DB::table('areas')->where('id', $client?->area_id)->first();

        DB::beginTransaction();
        try {
            if (is_null($payment->collection) && is_null($payment->type)) {
                // If it was just an empty placeholder row, delete it
                DB::table('clients_payments')->where('id', $payment->id)->delete();
            } else {
                // If it had a payment from SA area, restore it back to the SA reference number
                $dateCode = Carbon::parse($payment->due_date)->format('Y-m-d');
                $saAreaName = $homeArea?->areas_name ?? 'SA';
                $originalRefNo = $dateCode . '-' . strtoupper($saAreaName) . '-001';

                DB::table('clients_payments')
                    ->where('id', $payment->id)
                    ->update([
                        'reference_number' => $originalRefNo,
                        'client_area' => $homeArea?->id ?? $payment->client_area,
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            $msg = 'Client removed from current collection sheet.';
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
