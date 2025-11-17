<?php

namespace Dittofeed\Laravel\Listeners;

use Dittofeed\Laravel\Facades\Dittofeed;
use Illuminate\Auth\Events\Registered;

class TrackRegistration
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        try {
            $user = $event->user;

            // Identify the user
            Dittofeed::identify(
                (string) $user->getAuthIdentifier(),
                $this->getUserTraits($user)
            );

            // Track registration event
            Dittofeed::track(
                'User Registered',
                $this->getEventProperties($user),
                (string) $user->getAuthIdentifier()
            );
        } catch (\Exception $e) {
            if (config('dittofeed.debug')) {
                logger()->error('Failed to track registration', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get user traits for identification.
     */
    protected function getUserTraits($user): array
    {
        return array_filter([
            'email' => $user->email ?? null,
            'name' => $user->name ?? null,
            'created_at' => $user->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ]);
    }

    /**
     * Get event properties.
     */
    protected function getEventProperties($user): array
    {
        return array_filter([
            'user_id' => $user->getAuthIdentifier(),
            'email' => $user->email ?? null,
            'name' => $user->name ?? null,
        ]);
    }
}
