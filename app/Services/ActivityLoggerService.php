<?php
namespace App\Services;

use DeviceDetector\DeviceDetector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ActivityLoggerService
{
    /**
     * Cache TTL for device detection results (24 hours)
     */
    protected const DEVICE_CACHE_TTL = 86400;

    /**
     * Throttle period for authenticated user access logs (5 minutes)
     */
    protected const ACCESS_LOG_THROTTLE = 300;

    /**
     * Log HTTP access (public or authenticated)
     */
    public function logAccess(Request $request, Response $response): void
    {
        $user = Auth::check() ? Auth::user() : null;

        // Rate limiting: Skip logging for authenticated users if recently logged
        if ($user && !$this->shouldLogAccess($user->id)) {
            return;
        }

        $deviceDetails = $this->getDeviceDetails();
        $isBot = $deviceDetails['is_bot'] ?? false;

        // Determine visitor type
        $visitorType = $isBot ? 'bot' : ($user ? 'authenticated_user' : 'anonymous_visitor');

        activity('access')
            ->causedBy($user)
            ->withProperties([
                'visitor_type' => $visitorType,
                'is_bot' => $isBot,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'path' => $request->path(),
                'query_params' => $request->query(),
                'referrer' => $request->header('referer'),
                'status_code' => $response->getStatusCode(),
                'device' => $deviceDetails,
            ])
            ->log("Access: {$request->method()} {$request->path()}");

        // Mark this user as logged (throttle)
        if ($user) {
            $this->markAccessLogged($user->id);
        }
    }

    /**
     * Determine if we should log this access for an authenticated user
     *
     * @param int $userId
     * @return bool
     */
    protected function shouldLogAccess(int $userId): bool
    {
        $cacheKey = "access_log_throttle:{$userId}";

        return !Cache::has($cacheKey);
    }

    /**
     * Mark that we've logged access for this user (throttle future logs)
     *
     * @param int $userId
     * @return void
     */
    protected function markAccessLogged(int $userId): void
    {
        $cacheKey = "access_log_throttle:{$userId}";

        Cache::put($cacheKey, true, self::ACCESS_LOG_THROTTLE);
    }

    public function default(string $event, string $message, Model $model): void
    {
        $user = Auth::user();
        $oldValues = [];
        if ($event === 'updated') {
            $oldValues = array_intersect_key(
                $model->getOriginal(), 
                $model->getChanges()
            );
        }
        activity('default')
            ->performedOn($model)
            ->event($event)
            ->causedBy($user)
            ->withProperties([
                'attributes' => $model->getAttributes(),
                'old' => $oldValues,
                'device' => $this->getDeviceDetails(),
            ])
            ->log($message);
    }

    /**
     * Get device details with caching for performance
     *
     * Optimizations:
     * - Authenticated users skip bot detection (always false)
     * - Device detection results cached by User-Agent hash (24h)
     * - Reduces ~90% of DeviceDetector parsing calls
     *
     * @return array
     */
    public function getDeviceDetails(): array
    {
        $userAgent = request()->userAgent() ?? 'Unknown';
        $isAuthenticated = Auth::check();

        // Optimization: Authenticated users are never bots
        if ($isAuthenticated) {
            return $this->getCachedDeviceDetails($userAgent, skipBotDetection: true);
        }

        // Anonymous visitors: full detection with cache
        return $this->getCachedDeviceDetails($userAgent, skipBotDetection: false);
    }

    /**
     * Get cached device details or parse and cache them
     *
     * @param string $userAgent
     * @param bool $skipBotDetection
     * @return array
     */
    protected function getCachedDeviceDetails(string $userAgent, bool $skipBotDetection): array
    {
        // Create a cache key based on user agent hash and detection mode
        $cacheKey = 'device_details:' . md5($userAgent) . ':' . ($skipBotDetection ? 'auth' : 'anon');

        return Cache::remember($cacheKey, self::DEVICE_CACHE_TTL, function () use ($userAgent, $skipBotDetection) {
            // Performance optimization: Skip expensive bot detection for authenticated users
            if ($skipBotDetection) {
                return [
                    'ip' => request()->ip(),
                    'user_agent' => $userAgent,
                    'is_bot' => false,
                    'bot_name' => null,
                    'bot_category' => null,
                    'device_name' => 'Unknown',
                    'brand' => null,
                    'model' => null,
                    'os' => null,
                    'client' => null,
                ];
            }

            // Full device detection for anonymous visitors
            $dd = new DeviceDetector($userAgent);
            $dd->parse();

            return [
                'ip' => request()->ip(),
                'user_agent' => $dd->getUserAgent(),
                'is_bot' => $dd->isBot(),
                'bot_name' => $dd->getBot()['name'] ?? null,
                'bot_category' => $dd->getBot()['category'] ?? null,
                'device_name' => $dd->getDeviceName(),
                'brand' => $dd->getBrandName(),
                'model' => $dd->getModel(),
                'os' => $dd->getOs(),
                'client' => $dd->getClient(),
            ];
        });
    }
}
