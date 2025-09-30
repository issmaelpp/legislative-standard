<?php

namespace App\Listeners;

use App\Services\ActivityLoggerService;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
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
    public function handle(Login $event): void
    {
        $user = $event->user;
        $guard = $event->guard;

        activity('authentication')
            ->causedBy($user)
            ->event('login')
            ->withProperties([
                'guard' => $guard,
                'device' => $this->activityLogger->getDeviceDetails(),
            ])
            ->log("Login exitoso: {$user->name}");
    }
}
