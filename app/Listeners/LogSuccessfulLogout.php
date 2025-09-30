<?php

namespace App\Listeners;

use App\Services\ActivityLoggerService;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected ActivityLoggerService $activityLogger
    ) {}

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;
        $guard = $event->guard;

        activity('authentication')
            ->causedBy($user)
            ->event('logout')
            ->withProperties([
                'guard' => $guard,
                'device' => $this->activityLogger->getDeviceDetails(),
            ])
            ->log("Logout exitoso: {$user->name}");
    }
}
