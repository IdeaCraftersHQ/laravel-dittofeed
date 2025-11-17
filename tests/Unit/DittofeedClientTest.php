<?php

namespace Ideacrafters\Dittofeed\Tests\Unit;

use Ideacrafters\Dittofeed\DittofeedClient;
use Ideacrafters\Dittofeed\Exceptions\DittofeedException;
use Ideacrafters\Dittofeed\Exceptions\ValidationException;
use Ideacrafters\Dittofeed\Tests\TestCase;

class DittofeedClientTest extends TestCase
{
    protected DittofeedClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new DittofeedClient([
            'write_key' => 'test-key',
            'host' => 'https://test.dittofeed.com',
            'testing' => true,
            'timeout' => 30,
            'verify_ssl' => true,
            'context' => [
                'enabled' => false,
            ],
            'batch' => [
                'size' => 100,
                'auto_flush' => true,
            ],
        ]);
    }

    public function test_it_throws_exception_without_write_key(): void
    {
        $this->expectException(DittofeedException::class);
        $this->expectExceptionMessage('Write key is required');

        new DittofeedClient([
            'write_key' => '',
            'host' => 'https://test.dittofeed.com',
        ]);
    }

    public function test_it_can_identify_a_user(): void
    {
        $result = $this->client->identify([
            'userId' => 'user-123',
            'traits' => [
                'email' => 'john@example.com',
                'name' => 'John Doe',
            ],
        ]);

        $this->assertTrue($result['success']);
    }

    public function test_identify_requires_user_id_or_anonymous_id(): void
    {
        $this->expectException(ValidationException::class);

        $this->client->identify([
            'traits' => ['email' => 'john@example.com'],
        ]);
    }

    public function test_it_can_track_an_event(): void
    {
        $result = $this->client->track([
            'userId' => 'user-123',
            'event' => 'Purchase Complete',
            'properties' => [
                'amount' => 99.99,
                'currency' => 'USD',
            ],
        ]);

        $this->assertTrue($result['success']);
    }

    public function test_track_requires_event_name(): void
    {
        $this->expectException(ValidationException::class);

        $this->client->track([
            'userId' => 'user-123',
            'properties' => ['amount' => 99.99],
        ]);
    }

    public function test_it_can_track_a_page_view(): void
    {
        $result = $this->client->page([
            'userId' => 'user-123',
            'name' => 'Home Page',
            'url' => 'https://example.com',
        ]);

        $this->assertTrue($result['success']);
    }

    public function test_it_can_track_a_screen_view(): void
    {
        $result = $this->client->screen([
            'userId' => 'user-123',
            'name' => 'Home Screen',
        ]);

        $this->assertTrue($result['success']);
    }

    public function test_it_can_associate_user_with_group(): void
    {
        $result = $this->client->group([
            'userId' => 'user-123',
            'groupId' => 'company-abc',
            'traits' => [
                'name' => 'Acme Corporation',
                'plan' => 'enterprise',
            ],
        ]);

        $this->assertTrue($result['success']);
    }

    public function test_group_requires_group_id(): void
    {
        $this->expectException(ValidationException::class);

        $this->client->group([
            'userId' => 'user-123',
            'traits' => ['name' => 'Acme'],
        ]);
    }

    public function test_it_can_send_batch_events(): void
    {
        $events = [
            [
                'type' => 'identify',
                'userId' => 'user-123',
                'traits' => ['email' => 'john@example.com'],
            ],
            [
                'type' => 'track',
                'userId' => 'user-123',
                'event' => 'Page Viewed',
                'properties' => ['url' => 'https://example.com'],
            ],
        ];

        $result = $this->client->batch($events);

        $this->assertTrue($result['success']);
    }

    public function test_batch_requires_at_least_one_event(): void
    {
        $this->expectException(ValidationException::class);

        $this->client->batch([]);
    }
}
