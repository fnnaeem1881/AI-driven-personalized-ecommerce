<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for admin routes and login
        if ($request->is('admin*') || $request->is('login*') || $request->is('logout*')) {
            return $next($request);
        }

        $status = Setting::get('store_status', 'open');
        if ($status === 'maintenance') {
            // Allow admins through
            if (auth()->check() && auth()->user()->isAdmin()) {
                return $next($request);
            }
            $message = Setting::get('maintenance_message', 'We are performing scheduled maintenance. We\'ll be back shortly.');
            return response()->view('maintenance', ['message' => $message], 503);
        }

        return $next($request);
    }
}
