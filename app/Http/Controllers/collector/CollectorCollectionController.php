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

        // Get matched area records keyed by ID for display lookup
        $matchedAreas = DB::table('areas')
            ->whereIn('id', $allMatchedAreaIds)
            ->get()
            ->keyBy('id');

        // ✅ FIXED QUERY
        // Include:
        // - balance > 0
        // - loan already started
        // - include overdue (removed loan_to filter)
        $loans = DB::table('clients_loans as cl')
            ->join('clients as c', 'cl.client_id', '=', 'c.id')
            ->whereIn('c.area_id', $allMatchedAreaIds)
            ->whereDate('cl.loan_from', '<=', $selectedDate)
            ->select('cl.*', 'c.fullname', 'c.area_id')
            ->orderByDesc('cl.id')
            ->get();

        $payments = DB::table('clients_payments')
            ->whereDate('due_date', $selectedDate)
            ->whereIn('client_loans_id', $loans->pluck('id')->toArray())
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
                'loan' => $loan,
                'payment' => $payment,
                'is_overdue' => $isOverdue,
                'isPaid' => $isPaid
            ];
        })->filter(function ($c) {
            $balance = $c->loan->balance ?? 0;
            $status = $c->loan->status ?? '';
            return ($balance > 0 && $status !== 'paid') || ($c->payment && ($c->payment->collection ?? 0) > 0);
        })->values();

        $areas_name = $area->areas_name ?? 'UNKNOWN';
        $dateCode = \Carbon\Carbon::parse($selectedDate)->format('Y-m-d');
        $refNo = $dateCode . '-' . strtoupper($areas_name) . '-001';

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
            'selectedAreaId'
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
}
