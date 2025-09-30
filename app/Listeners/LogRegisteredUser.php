<?php

namespace App\Listeners;

use App\Services\ActivityLoggerService;
use Illuminate\Auth\Events\Registered;

class LogRegisteredUser
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
    public function handle(Registered $event): void
    {
        $user = $event->user;

        activity('authentication')
            ->causedBy($user)
            ->performedOn($user)
            ->event('registered')
            ->withProperties([
                'device' => $this->activityLogger->getDeviceDetails(),
            ])
            ->log("Nuevo registro: {$user->name} ({$user->email})");
    }
}
