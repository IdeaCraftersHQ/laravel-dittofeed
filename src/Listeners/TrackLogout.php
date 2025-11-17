<?php

namespace Ideacrafters\Dittofeed\Listeners;

use Ideacrafters\Dittofeed\Facades\Dittofeed;
use Illuminate\Auth\Events\Logout;

class TrackLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        try {
            $user = $event->user;

            // Track logout event
            Dittofeed::track(
                'User Logged Out',
                $this->getEventProperties($event),
                (string) $user->getAuthIdentifier()
            );
        } catch (\Exception $e) {
            if (config('dittofeed.debug')) {
                logger()->error('Failed to track logout', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get event properties.
     */
    protected function getEventProperties(Logout $event): array
    {
        return array_filter([
            'user_id' => $event->user->getAuthIdentifier(),
            'guard' => $event->guard ?? 'web',
        ]);
    }
}
