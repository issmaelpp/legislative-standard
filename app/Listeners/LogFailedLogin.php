<?php

namespace App\Listeners;

use App\Services\ActivityLoggerService;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
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
    public function handle(Failed $event): void
    {
        $credentials = $event->credentials;
        $guard = $event->guard;

        activity('authentication')
            ->event('login_failed')
            ->withProperties([
                'guard' => $guard,
                'email' => $credentials['email'] ?? null,
                'device' => $this->activityLogger->getDeviceDetails(),
            ])
            ->log("Intento de login fallido: {$credentials['email']}");
    }
}
