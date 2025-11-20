<?php

namespace Ideacrafters\Dittofeed;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Ideacrafters\Dittofeed\Exceptions\DittofeedException;
use Ideacrafters\Dittofeed\Exceptions\ValidationException;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class DittofeedClient
{
    protected Client $httpClient;

    protected string $writeKey;

    protected string $host;

    protected array $config;

    protected array $eventQueue = [];

    /**
     * Create a new Dittofeed client instance.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->writeKey = $config['write_key'] ?? '';
        $this->host = rtrim($config['host'] ?? 'https://app.dittofeed.com', '/');

        if (empty($this->writeKey)) {
            throw DittofeedException::configurationError('Write key is required');
        }

        $this->httpClient = new Client([
            'base_uri' => $this->host,
            'timeout' => $config['timeout'] ?? 30,
            'verify' => $config['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => $this->writeKey,
            ],
        ]);
    }

    /**
     * Identify a user with their traits.
     */
    public function identify(array $data): array
    {
        $this->validateIdentify($data);

        $payload = $this->buildPayload($data, [
            'userId' => $data['userId'] ?? null,
            'anonymousId' => $data['anonymousId'] ?? null,
            'traits' => $data['traits'] ?? [],
        ]);

        return $this->makeRequest('POST', '/api/public/apps/identify', $payload);
    }

    /**
     * Track a custom event.
     */
    public function track(array $data): array
    {
        $this->validateTrack($data);

        $payload = $this->buildPayload($data, [
            'userId' => $data['userId'] ?? null,
            'anonymousId' => $data['anonymousId'] ?? null,
            'event' => $data['event'],
            'properties' => $data['properties'] ?? [],
        ]);

        return $this->makeRequest('POST', '/api/public/apps/track', $payload);
    }

    /**
     * Track a page view.
     */
    public function page(array $data): array
    {
        $this->validatePage($data);

        $payload = $this->buildPayload($data, [
            'userId' => $data['userId'] ?? null,
            'anonymousId' => $data['anonymousId'] ?? null,
            'name' => $data['name'] ?? null,
            'properties' => array_merge(
                $data['properties'] ?? [],
                [
                    'url' => $data['url'] ?? null,
                    'path' => $data['path'] ?? null,
                    'referrer' => $data['referrer'] ?? null,
                    'title' => $data['title'] ?? null,
                ]
            ),
        ]);

        return $this->makeRequest('POST', '/api/public/apps/page', $payload);
    }

    /**
     * Track a screen view.
     */
    public function screen(array $data): array
    {
        $this->validateScreen($data);

        $payload = $this->buildPayload($data, [
            'userId' => $data['userId'] ?? null,
            'anonymousId' => $data['anonymousId'] ?? null,
            'name' => $data['name'] ?? null,
            'properties' => $data['properties'] ?? [],
        ]);

        return $this->makeRequest('POST', '/api/public/apps/screen', $payload);
    }

    /**
     * Associate a user with a group.
     */
    public function group(array $data): array
    {
        $this->validateGroup($data);

        $payload = $this->buildPayload($data, [
            'userId' => $data['userId'] ?? null,
            'anonymousId' => $data['anonymousId'] ?? null,
            'groupId' => $data['groupId'],
            'traits' => $data['traits'] ?? [],
        ]);

        return $this->makeRequest('POST', '/api/public/apps/group', $payload);
    }

    /**
     * Send a batch of events.
     */
    public function batch(array $events): array
    {
        if (empty($events)) {
            throw ValidationException::multiple(['events' => ['At least one event is required']]);
        }

        $batchSize = $this->config['batch']['size'] ?? 100;
        if (count($events) > $batchSize) {
            throw ValidationException::multiple([
                'events' => ["Batch size cannot exceed {$batchSize} events"],
            ]);
        }

        $payload = [
            'batch' => array_map(function ($event) {
                return $this->buildPayload($event, $event);
            }, $events),
        ];

        return $this->makeRequest('POST', '/api/public/apps/batch', $payload);
    }

    /**
     * Add event to queue for batch processing.
     */
    public function queueEvent(string $type, array $data): void
    {
        $this->eventQueue[] = array_merge(['type' => $type], $data);

        if ($this->shouldFlushQueue()) {
            $this->flush();
        }
    }

    /**
     * Flush the event queue.
     */
    public function flush(): void
    {
        if (empty($this->eventQueue)) {
            return;
        }

        try {
            $this->batch($this->eventQueue);
            $this->eventQueue = [];
        } catch (\Exception $e) {
            Log::error('Failed to flush Dittofeed event queue', [
                'error' => $e->getMessage(),
                'queue_size' => count($this->eventQueue),
            ]);
            throw $e;
        }
    }

    /**
     * Build the event payload with common fields.
     */
    protected function buildPayload(array $input, array $eventData): array
    {
        $payload = array_merge([
            'messageId' => $input['messageId'] ?? Uuid::uuid4()->toString(),
            'timestamp' => $input['timestamp'] ?? now()->toIso8601String(),
        ], $eventData);

        // Add context if enabled
        if ($this->config['context']['enabled'] ?? true) {
            $payload['context'] = $this->buildContext($input['context'] ?? []);
        }

        return array_filter($payload, function ($value, $key) {
            return $value !== null || 
            $key === 'anonymousId'|| // anonymousId is allowed to be null since its required by dittofeed service
            $key === 'context'; // context is allowed to be null since its required by dittofeed service
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Build context data for the event.
     */
    protected function buildContext(array $customContext = []): array
    {
        $context = $customContext??[];

        if (app()->runningInConsole()) {
            return $context;
        }

        $request = request();

        if ($this->config['context']['ip'] ?? true) {
            $context['ip'] = $request->ip();
        }

        if ($this->config['context']['user_agent'] ?? true) {
            $context['userAgent'] = $request->userAgent();
        }

        if ($this->config['context']['timezone'] ?? true) {
            $context['timezone'] = config('app.timezone');
        }

        if ($this->config['context']['locale'] ?? true) {
            $context['locale'] = app()->getLocale();
        }

        return $context;
    }

    /**
     * Make an HTTP request to the Dittofeed API.
     */
    protected function makeRequest(string $method, string $endpoint, array $data): array
    {
        if ($this->config['testing'] ?? false) {
            return ['success' => true, 'testing' => true];
        }

        $attempts = 0;
        $maxAttempts = $this->config['retry']['attempts'] ?? 3;
        $delay = $this->config['retry']['delay'] ?? 1000;
        $multiplier = $this->config['retry']['multiplier'] ?? 2;

        while ($attempts < $maxAttempts) {
            try {
                $response = $this->httpClient->request($method, $endpoint, [
                    'json' => $data,
                ]);

                $body = json_decode($response->getBody()->getContents(), true);

                if ($this->config['debug'] ?? false) {
                    Log::debug('Dittofeed API Request', [
                        'method' => $method,
                        'endpoint' => $endpoint,
                        'data' => $data,
                        'response' => $body,
                    ]);
                }

                return $body ?? ['success' => true];
            } catch (RequestException $e) {
                $attempts++;
                $statusCode = $e->getResponse()?->getStatusCode();

                // Don't retry on client errors (4xx) except 429 (rate limit)
                if ($statusCode && $statusCode >= 400 && $statusCode < 500 && $statusCode !== 429) {
                    $this->handleRequestException($e);
                }

                // Retry on 5xx errors and 429
                if ($attempts >= $maxAttempts) {
                    $this->handleRequestException($e);
                }

                // Wait before retrying
                usleep($delay * 1000);
                $delay *= $multiplier;
            } catch (GuzzleException $e) {
                throw DittofeedException::networkError(
                    'Failed to communicate with Dittofeed API: '.$e->getMessage(),
                    $e
                );
            }
        }

        throw DittofeedException::networkError('Maximum retry attempts exceeded');
    }

    /**
     * Handle request exceptions.
     */
    protected function handleRequestException(RequestException $e): void
    {
        $statusCode = $e->getResponse()?->getStatusCode();
        $body = $e->getResponse()?->getBody()->getContents();
        $message = $body ? json_decode($body, true)['message'] ?? $body : $e->getMessage();

        match ($statusCode) {
            401, 403 => throw DittofeedException::authenticationError($message),
            429 => throw DittofeedException::rateLimitError($message),
            default => throw DittofeedException::apiError($message, $statusCode ?? 0, $e),
        };
    }

    /**
     * Check if the event queue should be flushed.
     */
    protected function shouldFlushQueue(): bool
    {
        if (! ($this->config['batch']['auto_flush'] ?? true)) {
            return false;
        }

        $batchSize = $this->config['batch']['size'] ?? 100;

        return count($this->eventQueue) >= $batchSize;
    }

    /**
     * Validate identify data.
     */
    protected function validateIdentify(array $data): void
    {
        if (empty($data['userId']) && empty($data['anonymousId'])) {
            throw ValidationException::missingRequired('userId or anonymousId');
        }
    }

    /**
     * Validate track data.
     */
    protected function validateTrack(array $data): void
    {
        if (empty($data['userId']) && empty($data['anonymousId'])) {
            throw ValidationException::missingRequired('userId or anonymousId');
        }

        if (empty($data['event'])) {
            throw ValidationException::missingRequired('event');
        }
    }

    /**
     * Validate page data.
     */
    protected function validatePage(array $data): void
    {
        if (empty($data['userId']) && empty($data['anonymousId'])) {
            throw ValidationException::missingRequired('userId or anonymousId');
        }
    }

    /**
     * Validate screen data.
     */
    protected function validateScreen(array $data): void
    {
        if (empty($data['userId']) && empty($data['anonymousId'])) {
            throw ValidationException::missingRequired('userId or anonymousId');
        }
    }

    /**
     * Validate group data.
     */
    protected function validateGroup(array $data): void
    {
        if (empty($data['userId']) && empty($data['anonymousId'])) {
            throw ValidationException::missingRequired('userId or anonymousId');
        }

        if (empty($data['groupId'])) {
            throw ValidationException::missingRequired('groupId');
        }
    }
}
