<?php

namespace Ideacrafters\Dittofeed\Listeners;

use Ideacrafters\Dittofeed\Facades\Dittofeed;
use Illuminate\Auth\Events\Login;

class TrackLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        try {
            $user = $event->user;

            // Identify the user
            Dittofeed::identify(
                (string) $user->getAuthIdentifier(),
                $this->getUserTraits($user)
            );

            // Track login event
            Dittofeed::track(
                'User Logged In',
                $this->getEventProperties($event),
                (string) $user->getAuthIdentifier()
            );
        } catch (\Exception $e) {
            if (config('dittofeed.debug')) {
                logger()->error('Failed to track login', [
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
            'last_login_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get event properties.
     */
    protected function getEventProperties(Login $event): array
    {
        return array_filter([
            'user_id' => $event->user->getAuthIdentifier(),
            'guard' => $event->guard ?? 'web',
            'remember' => property_exists($event, 'remember') ? $event->remember : null,
        ]);
    }
}
