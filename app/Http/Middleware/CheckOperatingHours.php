<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class CheckOperatingHours
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Pwedeng i-disable pansamantala via .env (OPERATING_HOURS_ENABLED=false)
        if (env('OPERATING_HOURS_ENABLED', true) === false) {
            return $next($request);
        }

        // Bypasses: halimbawa kung admin ang naka-login at kailangan mag-maintain ng system
        // Tanggalin o i-comment ito kung nais na pati admin ay ma-shutdown.
        if (env('OPERATING_HOURS_ADMIN_BYPASS', true) && Session::get('role') === 'admin') {
            return $next($request);
        }

        // Bypasses para sa emergency secret query key (hal. ?override_key=ulc_secret)
        $overrideKey = env('OPERATING_HOURS_OVERRIDE_KEY');
        if (!empty($overrideKey) && $request->query('override_key') === $overrideKey) {
            return $next($request);
        }

        $now = Carbon::now('Asia/Manila');

        $role = Session::get('role');
        $isSecretary = ($role === 'secretary');

        // Oras ng pagbubukas: 8:00 AM (08:00:00)
        $openTime = $now->copy()->setTime(8, 0, 0);

        // Oras ng pagsasara:
        // Kapag secretary: 7:00 PM (19:00:00)
        // Kapag ibang role / client: 5:00 PM (17:00:00)
        $closeHour = $isSecretary ? 19 : 17;
        $closeTimeFormatted = $isSecretary ? '7:00 PM' : '5:00 PM';
        $offHoursFormatted = $isSecretary ? '7:01 PM – 7:59 AM' : '5:01 PM – 7:59 AM';
        $closeTime = $now->copy()->setTime($closeHour, 0, 0);

        // Kung bago mag 8:00 AM o lagpas na sa closing time
        if ($now->lt($openTime) || $now->gt($closeTime)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'closed',
                    'message' => "System is closed. Operating hours are from 8:00 AM to {$closeTimeFormatted} only."
                ], 503);
            }

            return response()->view('errors.system-closed', [
                'currentTime' => $now->format('h:i:s A'),
                'currentDate' => $now->format('F d, Y'),
                'operatingHours' => "8:00 AM – {$closeTimeFormatted}",
                'offHours' => $offHoursFormatted,
                'closeTimeFormatted' => $closeTimeFormatted,
            ], 503);
        }

        return $next($request);
    }
}
