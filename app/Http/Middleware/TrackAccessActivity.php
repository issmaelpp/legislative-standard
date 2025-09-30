<?php

namespace App\Http\Middleware;

use App\Services\ActivityLoggerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAccessActivity
{
    /**
     * Handle an incoming request.
     *
     * Performance optimizations:
     * - Rate limiting for authenticated users (1 log per 5 minutes)
     * - Device detection cached for 24 hours
     * - Bot detection skipped for authenticated users
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Process the request first
        $response = $next($request);

        // Skip tracking for internal routes (Livewire, Flux, asset routes)
        if ($this->shouldSkipTracking($request)) {
            return $response;
        }

        // Log the access after the request has been processed
        // Note: ActivityLoggerService now implements rate limiting and caching internally
        try {
            app(ActivityLoggerService::class)->logAccess($request, $response);
        } catch (\Exception $e) {
            // Fail silently to avoid breaking the application
            report($e);
        }

        return $response;
    }

    /**
     * Determine if we should skip tracking for this request
     */
    protected function shouldSkipTracking(Request $request): bool
    {
        // Skip internal framework routes (Livewire updates, Flux assets, etc.)
        $skipPaths = [
            'livewire/*',           // Livewire AJAX updates
            'flux/*',               // Flux framework assets
            'storage/*',            // Storage files
            'up',                   // Health check endpoint
            'login',                // Login page & POST (tracked by LogSuccessfulLogin listener)
            'logout',               // Logout (tracked by LogSuccessfulLogout listener)
            'register',             // Register (tracked by LogRegisteredUser listener)
            'forgot-password',      // Password reset pages
            'reset-password/*',     // Password reset pages
            'verify-email',         // Email verification
            'verify-email/*',       // Email verification
        ];

        foreach ($skipPaths as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        // Optional: Skip tracking for specific bots (uncomment if needed)
        // This is useful if you only want to track human visitors
        /*
        $dd = new \DeviceDetector\DeviceDetector($request->userAgent());
        $dd->parse();

        if ($dd->isBot()) {
            // Skip all bots
            return true;

            // Or skip only specific bot categories:
            // $botCategory = $dd->getBot()['category'] ?? null;
            // return in_array($botCategory, ['Search bot', 'Crawler']);
        }
        */

        return false;
    }
}