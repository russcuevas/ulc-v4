<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('getAreaPnDetails')) {
    /**
     * Get the Last PN and calculate the Next PN (Only for Manila Area).
     * For other areas, returns global latest PN and empty next PN for manual entry.
     *
     * @param int|string $areaId
     * @return array
     */
    function getAreaPnDetails($areaId)
    {
        $globalLastPn = DB::table('clients_loans')->latest('id')->value('pn_number') ?? 'N/A';

        $area = DB::table('areas')->where('id', $areaId)->first();
        if (!$area) {
            return [
                'area_id' => $areaId,
                'areas_name' => '',
                'location_name' => '',
                'is_manila' => false,
                'last_pn' => $globalLastPn,
                'next_pn' => '',
            ];
        }

        $areas_name = trim($area->areas_name ?? '');
        $location_name = trim($area->location_name ?? '');
        $isManila = stripos($location_name, 'Manila') !== false;

        // If NOT Manila Area (e.g. Caloocan, Financial Counselor, etc.)
        if (!$isManila) {
            return [
                'area_id' => $areaId,
                'areas_name' => $areas_name,
                'location_name' => $location_name,
                'is_manila' => false,
                'last_pn' => $globalLastPn,
                'next_pn' => '', // Blank for other areas
            ];
        }

        // Manila Area logic: Area-specific last PN and auto-incremented next PN
        $currentYear = date('Y');

        $matchedAreaIds = DB::table('areas')
            ->where('location_name', $location_name)
            ->where('areas_name', $areas_name)
            ->pluck('id')
            ->toArray();

        if (empty($matchedAreaIds)) {
            $matchedAreaIds = [$areaId];
        }

        // Find the latest loan recorded for clients in this specific Manila area
        $lastLoan = DB::table('clients_loans')
            ->join('clients', 'clients_loans.client_id', '=', 'clients.id')
            ->whereIn('clients.area_id', $matchedAreaIds)
            ->orderBy('clients_loans.id', 'desc')
            ->select('clients_loans.pn_number')
            ->first();

        $lastPn = ($lastLoan && !empty(trim($lastLoan->pn_number))) ? trim($lastLoan->pn_number) : null;

        if ($lastPn && preg_match('/(\d+)$/', $lastPn, $matches)) {
            $lastNumberStr = $matches[1];
            $padLength = max(4, strlen($lastNumberStr));
            $nextNumber = intval($lastNumberStr) + 1;
            $nextNumberPadded = str_pad($nextNumber, $padLength, '0', STR_PAD_LEFT);
            $nextPn = "{$currentYear}-{$areas_name}-{$nextNumberPadded}";
        } else {
            $nextPn = "{$currentYear}-{$areas_name}-0001";
        }

        return [
            'area_id' => $areaId,
            'areas_name' => $areas_name,
            'location_name' => $location_name,
            'is_manila' => true,
            'last_pn' => $lastPn ?? 'N/A',
            'next_pn' => $nextPn,
        ];
    }
}

if (!function_exists('getAreaLastPn')) {
    /**
     * Helper to get the last PN string for a specific area.
     *
     * @param int|string $areaId
     * @return string
     */
    function getAreaLastPn($areaId)
    {
        return getAreaPnDetails($areaId)['last_pn'];
    }
}

if (!function_exists('getAreaNextPn')) {
    /**
     * Helper to get the next sequential PN string for Manila area, or blank for others.
     *
     * @param int|string $areaId
     * @return string
     */
    function getAreaNextPn($areaId)
    {
        return getAreaPnDetails($areaId)['next_pn'];
    }
}
