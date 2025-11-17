<?php

namespace Dittofeed\Laravel\Tests\Feature;

use Dittofeed\Laravel\Facades\Dittofeed;
use Dittofeed\Laravel\Tests\TestCase;

class FakeDittofeedTest extends TestCase
{
    public function test_fake_can_assert_identify_was_called(): void
    {
        $fake = Dittofeed::fake();

        Dittofeed::identify('user-123', ['email' => 'john@example.com']);

        $fake->assertIdentified('user-123');
        $fake->assertIdentifyCount(1);
    }

    public function test_fake_can_assert_track_was_called(): void
    {
        $fake = Dittofeed::fake();

        Dittofeed::track('Purchase Complete', ['amount' => 99.99], 'user-123');

        $fake->assertTracked('Purchase Complete', ['amount' => 99.99], 'user-123');
        $fake->assertTrackCount(1);
    }

    public function test_fake_can_assert_event_was_not_tracked(): void
    {
        $fake = Dittofeed::fake();

        Dittofeed::track('Event A', [], 'user-123');

        $fake->assertTracked('Event A');
        $fake->assertNotTracked('Event B');
    }

    public function test_fake_can_assert_page_was_viewed(): void
    {
        $fake = Dittofeed::fake();

        Dittofeed::page('Home Page', ['url' => 'https://example.com'], 'user-123');

        $fake->assertPageViewed('Home Page');
    }

    public function test_fake_can_assert_group_was_called(): void
    {
        $fake = Dittofeed::fake();

        Dittofeed::group('company-abc', ['name' => 'Acme'], 'user-123');

        $fake->assertGrouped('company-abc', ['name' => 'Acme'], 'user-123');
    }

    public function test_fake_can_assert_nothing_was_called(): void
    {
        $fake = Dittofeed::fake();

        $fake->assertNothingCalled();
    }

    public function test_fake_tracks_all_calls(): void
    {
        $fake = Dittofeed::fake();

        Dittofeed::identify('user-123', ['email' => 'john@example.com']);
        Dittofeed::track('Event A', [], 'user-123');
        Dittofeed::track('Event B', [], 'user-123');
        Dittofeed::page('Page A', [], 'user-123');

        $this->assertCount(1, $fake->identifyCalls());
        $this->assertCount(2, $fake->trackCalls());
        $this->assertCount(1, $fake->pageCalls());
    }
}
