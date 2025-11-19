<?php

namespace Ideacrafters\Dittofeed;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Ramsey\Uuid\Uuid;

class DittofeedManager
{
    protected DittofeedClient $client;
    protected ?AdminClient $adminClient = null;
    protected array $config;
    protected ?\Closure $userIdResolver = null;

    /**
     * Create a new Dittofeed manager instance.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new DittofeedClient($config);

        // Initialize admin client if admin key is configured
        if (!empty($config['admin_key'])) {
            try {
                $this->adminClient = new AdminClient($config);
            } catch (\Exception $e) {
                // Admin client is optional, so we just log and continue
                if ($config['debug'] ?? false) {
                    logger()->warning('Failed to initialize Dittofeed admin client', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->userIdResolver = isset($config['user_id_resolver']) 
            ? \Closure::fromCallable($config['user_id_resolver']) 
            : null;
    }

    /**
     * Identify a user with their traits.
     */
    public function identify(?string $userId = null, array $traits = [], ?string $anonymousId = null): array
    {
        $data = [
            'userId' => $userId ?? $this->resolveUserId(),
            'anonymousId' => $anonymousId ?? $this->getAnonymousId(),
            'traits' => $traits,
        ];

        return $this->shouldQueue()
            ? $this->queueEvent('identify', $data)
            : $this->client->identify($data);
    }

    /**
     * Track a custom event.
     */
    public function track(string $event, array $properties = [], ?string $userId = null, ?string $anonymousId = null): array
    {
        $data = [
            'userId' => $userId ?? $this->resolveUserId(),
            'anonymousId' => $anonymousId ?? $this->getAnonymousId(),
            'event' => $event,
            'properties' => $properties,
        ];

        return $this->shouldQueue()
            ? $this->queueEvent('track', $data)
            : $this->client->track($data);
    }

    /**
     * Track a page view.
     */
    public function page(?string $name = null, array $properties = [], ?string $userId = null, ?string $anonymousId = null): array
    {
        $data = [
            'userId' => $userId ?? $this->resolveUserId(),
            'anonymousId' => $anonymousId ?? $this->getAnonymousId(),
            'name' => $name,
            'properties' => $properties,
        ];

        // Add URL properties if not set and we're in a web context
        if (!app()->runningInConsole() && request()) {
            $data['url'] = $data['url'] ?? request()->fullUrl();
            $data['path'] = $data['path'] ?? request()->path();
            $data['referrer'] = $data['referrer'] ?? request()->header('referer');
            $data['title'] = $data['title'] ?? $name;
        }

        return $this->shouldQueue()
            ? $this->queueEvent('page', $data)
            : $this->client->page($data);
    }

    /**
     * Track a screen view.
     */
    public function screen(?string $name = null, array $properties = [], ?string $userId = null, ?string $anonymousId = null): array
    {
        $data = [
            'userId' => $userId ?? $this->resolveUserId(),
            'anonymousId' => $anonymousId ?? $this->getAnonymousId(),
            'name' => $name,
            'properties' => $properties,
        ];

        return $this->shouldQueue()
            ? $this->queueEvent('screen', $data)
            : $this->client->screen($data);
    }

    /**
     * Associate a user with a group.
     */
    public function group(string $groupId, array $traits = [], ?string $userId = null, ?string $anonymousId = null): array
    {
        $data = [
            'userId' => $userId ?? $this->resolveUserId(),
            'anonymousId' => $anonymousId ?? $this->getAnonymousId(),
            'groupId' => $groupId,
            'traits' => $traits,
        ];

        return $this->shouldQueue()
            ? $this->queueEvent('group', $data)
            : $this->client->group($data);
    }

    /**
     * Send a batch of events.
     */
    public function batch(array $events): array
    {
        return $this->client->batch($events);
    }

    /**
     * Flush the event queue.
     */
    public function flush(): void
    {
        $this->client->flush();
    }

    /**
     * Get the admin client.
     */
    public function admin(): AdminClient
    {
        if (!$this->adminClient) {
            throw new \RuntimeException('Admin client is not configured. Please set DITTOFEED_ADMIN_KEY and DITTOFEED_WORKSPACE_ID in your environment.');
        }

        return $this->adminClient;
    }

    /**
     * Get the underlying client instance.
     */
    public function getClient(): DittofeedClient
    {
        return $this->client;
    }

    /**
     * Set a custom user ID resolver.
     */
    public function resolveUserIdUsing(callable $callback): self
    {
        $this->userIdResolver = \Closure::fromCallable($callback);

        return $this;
    }

    /**
     * Resolve the current user ID.
     */
    protected function resolveUserId(): ?string
    {
        if ($this->userIdResolver) {
            return call_user_func($this->userIdResolver);
        }

        return Auth::check() ? (string) Auth::id() : null;
    }

    /**
     * Get or create an anonymous ID for tracking unauthenticated users.
     */
    protected function getAnonymousId(): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        $cookieName = $this->config['anonymous_id']['cookie_name'] ?? 'dittofeed_anonymous_id';
        $anonymousId = Cookie::get($cookieName);

        if (!$anonymousId) {
            $anonymousId = Uuid::uuid4()->toString();
            $lifetime = $this->config['anonymous_id']['cookie_lifetime'] ?? 525600; // 1 year

            Cookie::queue(
                Cookie::make($cookieName, $anonymousId, $lifetime, '/', null, true, false)
            );
        }

        return $anonymousId;
    }

    /**
     * Queue an event for asynchronous processing.
     */
    protected function queueEvent(string $type, array $data): array
    {
        $job = new \Ideacrafters\Dittofeed\Jobs\SendDittofeedEvent($type, $data);

        $connection = $this->config['queue']['connection'] ?? null;
        $queue = $this->config['queue']['queue'] ?? 'default';

        if ($connection) {
            $job->onConnection($connection)->onQueue($queue);
        } else {
            $job->onQueue($queue);
        }

        dispatch($job);

        return ['success' => true, 'queued' => true];
    }

    /**
     * Determine if events should be queued.
     */
    protected function shouldQueue(): bool
    {
        return $this->config['queue']['enabled'] ?? false;
    }
}
