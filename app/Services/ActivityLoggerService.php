<?php
namespace App\Services;

use DeviceDetector\DeviceDetector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLoggerService
{
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
        $dd->skipBotDetection();
        $dd->parse();
        return [
            'ip' => request()->ip(),
            'user_agent' => $dd->getUserAgent(),
            'device_name' => $dd->getDeviceName(),
            'brand' => $dd->getBrandName(),
            'model' => $dd->getModel(),
            'os' => $dd->getOs(),
            'client' => $dd->getClient(),
        ];
    }
}
