<?php

namespace Dittofeed\Laravel;

use Dittofeed\Laravel\Exceptions\DittofeedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AdminClient
{
    protected Client $httpClient;
    protected string $adminKey;
    protected string $workspaceId;
    protected string $host;
    protected array $config;

    /**
     * Create a new Dittofeed admin client instance.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->adminKey = $config['admin_key'] ?? '';
        $this->workspaceId = $config['workspace_id'] ?? '';
        $this->host = rtrim($config['host'] ?? 'https://app.dittofeed.com', '/');

        if (empty($this->adminKey)) {
            throw DittofeedException::configurationError('Admin key is required for admin operations');
        }

        if (empty($this->workspaceId)) {
            throw DittofeedException::configurationError('Workspace ID is required for admin operations');
        }

        $this->httpClient = new Client([
            'base_uri' => $this->host,
            'timeout' => $config['timeout'] ?? 30,
            'verify' => $config['verify_ssl'] ?? true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->adminKey,
            ],
        ]);
    }

    /**
     * Get all templates.
     */
    public function getTemplates(): array
    {
        return $this->makeRequest('GET', "/api/admin/workspaces/{$this->workspaceId}/templates");
    }

    /**
     * Create a new template.
     */
    public function createTemplate(array $data): array
    {
        return $this->makeRequest('POST', "/api/admin/workspaces/{$this->workspaceId}/templates", $data);
    }

    /**
     * Get a specific template.
     */
    public function getTemplate(string $templateId): array
    {
        return $this->makeRequest('GET', "/api/admin/workspaces/{$this->workspaceId}/templates/{$templateId}");
    }

    /**
     * Update a template.
     */
    public function updateTemplate(string $templateId, array $data): array
    {
        return $this->makeRequest('PUT', "/api/admin/workspaces/{$this->workspaceId}/templates/{$templateId}", $data);
    }

    /**
     * Delete a template.
     */
    public function deleteTemplate(string $templateId): array
    {
        return $this->makeRequest('DELETE', "/api/admin/workspaces/{$this->workspaceId}/templates/{$templateId}");
    }

    /**
     * Get all segments.
     */
    public function getSegments(): array
    {
        return $this->makeRequest('GET', "/api/admin/workspaces/{$this->workspaceId}/segments");
    }

    /**
     * Create a new segment.
     */
    public function createSegment(array $data): array
    {
        return $this->makeRequest('POST', "/api/admin/workspaces/{$this->workspaceId}/segments", $data);
    }

    /**
     * Get a specific segment.
     */
    public function getSegment(string $segmentId): array
    {
        return $this->makeRequest('GET', "/api/admin/workspaces/{$this->workspaceId}/segments/{$segmentId}");
    }

    /**
     * Update a segment.
     */
    public function updateSegment(string $segmentId, array $data): array
    {
        return $this->makeRequest('PUT', "/api/admin/workspaces/{$this->workspaceId}/segments/{$segmentId}", $data);
    }

    /**
     * Delete a segment.
     */
    public function deleteSegment(string $segmentId): array
    {
        return $this->makeRequest('DELETE', "/api/admin/workspaces/{$this->workspaceId}/segments/{$segmentId}");
    }

    /**
     * Get all journeys.
     */
    public function getJourneys(): array
    {
        return $this->makeRequest('GET', "/api/admin/workspaces/{$this->workspaceId}/journeys");
    }

    /**
     * Create a new journey.
     */
    public function createJourney(array $data): array
    {
        return $this->makeRequest('POST', "/api/admin/workspaces/{$this->workspaceId}/journeys", $data);
    }

    /**
     * Get a specific journey.
     */
    public function getJourney(string $journeyId): array
    {
        return $this->makeRequest('GET', "/api/admin/workspaces/{$this->workspaceId}/journeys/{$journeyId}");
    }

    /**
     * Update a journey.
     */
    public function updateJourney(string $journeyId, array $data): array
    {
        return $this->makeRequest('PUT', "/api/admin/workspaces/{$this->workspaceId}/journeys/{$journeyId}", $data);
    }

    /**
     * Delete a journey.
     */
    public function deleteJourney(string $journeyId): array
    {
        return $this->makeRequest('DELETE', "/api/admin/workspaces/{$this->workspaceId}/journeys/{$journeyId}");
    }

    /**
     * Get user properties.
     */
    public function getUserProperties(): array
    {
        return $this->makeRequest('GET', "/api/admin/workspaces/{$this->workspaceId}/user-properties");
    }

    /**
     * Create a user property.
     */
    public function createUserProperty(array $data): array
    {
        return $this->makeRequest('POST', "/api/admin/workspaces/{$this->workspaceId}/user-properties", $data);
    }

    /**
     * Delete user data.
     */
    public function deleteUserData(string $userId): array
    {
        return $this->makeRequest('DELETE', "/api/admin/workspaces/{$this->workspaceId}/users/{$userId}");
    }

    /**
     * Send a broadcast message.
     */
    public function sendBroadcast(array $data): array
    {
        return $this->makeRequest('POST', "/api/admin/workspaces/{$this->workspaceId}/broadcasts", $data);
    }

    /**
     * Get broadcasts.
     */
    public function getBroadcasts(): array
    {
        return $this->makeRequest('GET', "/api/admin/workspaces/{$this->workspaceId}/broadcasts");
    }

    /**
     * Get a specific broadcast.
     */
    public function getBroadcast(string $broadcastId): array
    {
        return $this->makeRequest('GET', "/api/admin/workspaces/{$this->workspaceId}/broadcasts/{$broadcastId}");
    }

    /**
     * Make an HTTP request to the Dittofeed Admin API.
     */
    protected function makeRequest(string $method, string $endpoint, ?array $data = null): array
    {
        if ($this->config['testing'] ?? false) {
            return ['success' => true, 'testing' => true];
        }

        try {
            $options = [];
            if ($data !== null) {
                $options['json'] = $data;
            }

            $response = $this->httpClient->request($method, $endpoint, $options);
            $body = json_decode($response->getBody()->getContents(), true);

            if ($this->config['debug'] ?? false) {
                Log::debug('Dittofeed Admin API Request', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'data' => $data,
                    'response' => $body,
                ]);
            }

            return $body ?? ['success' => true];
        } catch (RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode();
            $body = $e->getResponse()?->getBody()->getContents();
            $message = $body ? json_decode($body, true)['message'] ?? $body : $e->getMessage();

            match ($statusCode) {
                401, 403 => throw DittofeedException::authenticationError($message),
                429 => throw DittofeedException::rateLimitError($message),
                default => throw DittofeedException::apiError($message, $statusCode ?? 0, $e),
            };
        } catch (GuzzleException $e) {
            throw DittofeedException::networkError(
                'Failed to communicate with Dittofeed Admin API: ' . $e->getMessage(),
                $e
            );
        }
    }
}
