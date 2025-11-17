<?php

namespace Dittofeed\Laravel\Traits;

use Dittofeed\Laravel\Facades\Dittofeed;

trait TracksDittofeedEvents
{
    /**
     * Boot the trait.
     */
    public static function bootTracksDittofeedEvents(): void
    {
        if (!config('dittofeed.auto_track.enabled') || !config('dittofeed.auto_track.model_events')) {
            return;
        }

        // Track model creation
        static::created(function ($model) {
            $model->trackDittofeedEvent('created');
        });

        // Track model updates
        static::updated(function ($model) {
            $model->trackDittofeedEvent('updated');
        });

        // Track model deletion
        static::deleted(function ($model) {
            $model->trackDittofeedEvent('deleted');
        });
    }

    /**
     * Track a Dittofeed event for this model.
     */
    public function trackDittofeedEvent(string $eventType): void
    {
        try {
            // Get event name from configuration or use default
            $eventName = $this->getDittofeedEventName($eventType);

            if (!$eventName) {
                return;
            }

            // Identify the user with traits
            $this->identifyInDittofeed();

            // Track the event
            Dittofeed::track(
                $eventName,
                $this->getDittofeedEventProperties($eventType),
                $this->getDittofeedUserId()
            );
        } catch (\Exception $e) {
            // Fail silently to not break the application
            if (config('dittofeed.debug')) {
                logger()->error('Failed to track Dittofeed event', [
                    'model' => get_class($this),
                    'event_type' => $eventType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Identify this model in Dittofeed.
     */
    public function identifyInDittofeed(): void
    {
        try {
            Dittofeed::identify(
                $this->getDittofeedUserId(),
                $this->getDittofeedTraits()
            );
        } catch (\Exception $e) {
            if (config('dittofeed.debug')) {
                logger()->error('Failed to identify in Dittofeed', [
                    'model' => get_class($this),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Track a custom event for this model.
     */
    public function trackCustomEvent(string $event, array $properties = []): void
    {
        Dittofeed::track(
            $event,
            array_merge($this->getDittofeedEventProperties('custom'), $properties),
            $this->getDittofeedUserId()
        );
    }

    /**
     * Get the event name for a given event type.
     */
    protected function getDittofeedEventName(string $eventType): ?string
    {
        // Check if model has custom event mapping
        if (property_exists($this, 'dittofeedEvents') && isset($this->dittofeedEvents[$eventType])) {
            return $this->dittofeedEvents[$eventType];
        }

        // Use default event names
        $modelName = class_basename($this);

        return match ($eventType) {
            'created' => "{$modelName} Created",
            'updated' => "{$modelName} Updated",
            'deleted' => "{$modelName} Deleted",
            default => null,
        };
    }

    /**
     * Get the properties to send with the event.
     */
    protected function getDittofeedEventProperties(string $eventType): array
    {
        $properties = [
            'model_type' => get_class($this),
            'model_id' => $this->getKey(),
            'event_type' => $eventType,
        ];

        // Add custom properties if defined
        if (property_exists($this, 'dittofeedProperties') && is_callable($this->dittofeedProperties)) {
            $properties = array_merge($properties, call_user_func($this->dittofeedProperties, $this));
        } elseif (method_exists($this, 'getDittofeedProperties')) {
            $properties = array_merge($properties, $this->getDittofeedProperties());
        }

        return $properties;
    }

    /**
     * Get the user ID for Dittofeed tracking.
     */
    protected function getDittofeedUserId(): ?string
    {
        // If this model is the User model, return its ID
        if (method_exists($this, 'getAuthIdentifier')) {
            return (string) $this->getKey();
        }

        // Check if model has a user relationship
        if (method_exists($this, 'user') && $this->user) {
            return (string) $this->user->getKey();
        }

        // Check if model has a user_id attribute
        if (isset($this->user_id)) {
            return (string) $this->user_id;
        }

        // Fall back to authenticated user
        return auth()->check() ? (string) auth()->id() : null;
    }

    /**
     * Get the traits to send to Dittofeed.
     */
    protected function getDittofeedTraits(): array
    {
        // Check if model specifies which attributes to send as traits
        if (property_exists($this, 'dittofeedTraits')) {
            return $this->only($this->dittofeedTraits);
        }

        // For User models, send common attributes
        if (method_exists($this, 'getAuthIdentifier')) {
            return array_filter([
                'email' => $this->email ?? null,
                'name' => $this->name ?? null,
                'created_at' => $this->created_at?->toIso8601String(),
            ]);
        }

        return [];
    }
}
