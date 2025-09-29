<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLoggerService;
use App\Services\WordFormatterService;

class UserObserver
{
    public function __construct(
        protected ActivityLoggerService $activityLog,
        protected WordFormatterService $wordFormatter,
    ) {}

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $message = $this->wordFormatter->buildMessageWithGender('created', $user);
        $this->activityLog->default('created', $message, $user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged('deleted_at') && is_null($user->deleted_at)) {
            return;
        }
        $message = $this->wordFormatter->buildMessageWithGender('updated', $user);
        $this->activityLog->default('updated', $message, $user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        if ($user->isForceDeleting()) {
            return;
        }
        $message = $this->wordFormatter->buildMessageWithGender('deleted', $user);
        $this->activityLog->default('deleted', $message, $user);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $message = $this->wordFormatter->buildMessageWithGender('restored', $user);
        $this->activityLog->default('restored', $message, $user);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $message = $this->wordFormatter->buildMessageWithGender('force_deleted', $user);
        $this->activityLog->default('force_deleted', $message, $user);
    }
}
