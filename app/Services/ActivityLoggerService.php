<?php
namespace App\Services;

use DeviceDetector\DeviceDetector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActivityLoggerService
{
    /**
     * Log HTTP access (public or authenticated)
     */
    public function logAccess(Request $request, Response $response): void
    {
        $user = Auth::check() ? Auth::user() : null;
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

    protected function getDeviceDetails(): array
    {
        $dd = new DeviceDetector(request()->userAgent());
        // Do NOT skip bot detection for access logging
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
    }
}
