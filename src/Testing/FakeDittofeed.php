<?php

namespace Dittofeed\Laravel\Testing;

use PHPUnit\Framework\Assert as PHPUnit;

class FakeDittofeed
{
    protected array $identifyCalls = [];
    protected array $trackCalls = [];
    protected array $pageCalls = [];
    protected array $screenCalls = [];
    protected array $groupCalls = [];
    protected array $batchCalls = [];

    /**
     * Fake identify call.
     */
    public function identify(?string $userId = null, array $traits = [], ?string $anonymousId = null): array
    {
        $this->identifyCalls[] = compact('userId', 'traits', 'anonymousId');

        return ['success' => true, 'fake' => true];
    }

    /**
     * Fake track call.
     */
    public function track(string $event, array $properties = [], ?string $userId = null, ?string $anonymousId = null): array
    {
        $this->trackCalls[] = compact('event', 'properties', 'userId', 'anonymousId');

        return ['success' => true, 'fake' => true];
    }

    /**
     * Fake page call.
     */
    public function page(?string $name = null, array $properties = [], ?string $userId = null, ?string $anonymousId = null): array
    {
        $this->pageCalls[] = compact('name', 'properties', 'userId', 'anonymousId');

        return ['success' => true, 'fake' => true];
    }

    /**
     * Fake screen call.
     */
    public function screen(?string $name = null, array $properties = [], ?string $userId = null, ?string $anonymousId = null): array
    {
        $this->screenCalls[] = compact('name', 'properties', 'userId', 'anonymousId');

        return ['success' => true, 'fake' => true];
    }

    /**
     * Fake group call.
     */
    public function group(string $groupId, array $traits = [], ?string $userId = null, ?string $anonymousId = null): array
    {
        $this->groupCalls[] = compact('groupId', 'traits', 'userId', 'anonymousId');

        return ['success' => true, 'fake' => true];
    }

    /**
     * Fake batch call.
     */
    public function batch(array $events): array
    {
        $this->batchCalls[] = $events;

        return ['success' => true, 'fake' => true];
    }

    /**
     * Fake flush call.
     */
    public function flush(): void
    {
        // No-op for fake
    }

    /**
     * Fake admin client.
     */
    public function admin(): self
    {
        return $this;
    }

    /**
     * Fake getClient call.
     */
    public function getClient(): self
    {
        return $this;
    }

    /**
     * Fake resolveUserIdUsing call.
     */
    public function resolveUserIdUsing(callable $callback): self
    {
        return $this;
    }

    // Assertion methods

    /**
     * Assert that identify was called.
     */
    public function assertIdentified(?string $userId = null, ?array $traits = null): void
    {
        $count = count($this->identifyCalls);

        PHPUnit::assertTrue(
            $count > 0,
            "Expected identify to be called, but it was not called."
        );

        if ($userId !== null || $traits !== null) {
            $matching = $this->filterCalls($this->identifyCalls, compact('userId', 'traits'));

            PHPUnit::assertNotEmpty(
                $matching,
                "Expected identify to be called with matching parameters, but no matching calls were found."
            );
        }
    }

    /**
     * Assert that identify was not called.
     */
    public function assertNotIdentified(): void
    {
        PHPUnit::assertEmpty(
            $this->identifyCalls,
            "Expected identify to not be called, but it was called " . count($this->identifyCalls) . " time(s)."
        );
    }

    /**
     * Assert that track was called with a specific event.
     */
    public function assertTracked(string $event, ?array $properties = null, ?string $userId = null): void
    {
        $matching = array_filter($this->trackCalls, function ($call) use ($event, $properties, $userId) {
            if ($call['event'] !== $event) {
                return false;
            }

            if ($properties !== null && $call['properties'] !== $properties) {
                return false;
            }

            if ($userId !== null && $call['userId'] !== $userId) {
                return false;
            }

            return true;
        });

        PHPUnit::assertNotEmpty(
            $matching,
            "Expected event '{$event}' to be tracked, but it was not found."
        );
    }

    /**
     * Assert that track was not called with a specific event.
     */
    public function assertNotTracked(string $event): void
    {
        $matching = array_filter($this->trackCalls, function ($call) use ($event) {
            return $call['event'] === $event;
        });

        PHPUnit::assertEmpty(
            $matching,
            "Expected event '{$event}' to not be tracked, but it was tracked " . count($matching) . " time(s)."
        );
    }

    /**
     * Assert that page was called.
     */
    public function assertPageViewed(?string $name = null, ?array $properties = null): void
    {
        $count = count($this->pageCalls);

        PHPUnit::assertTrue(
            $count > 0,
            "Expected page to be called, but it was not called."
        );

        if ($name !== null || $properties !== null) {
            $matching = $this->filterCalls($this->pageCalls, compact('name', 'properties'));

            PHPUnit::assertNotEmpty(
                $matching,
                "Expected page to be called with matching parameters, but no matching calls were found."
            );
        }
    }

    /**
     * Assert that screen was called.
     */
    public function assertScreenViewed(?string $name = null, ?array $properties = null): void
    {
        $count = count($this->screenCalls);

        PHPUnit::assertTrue(
            $count > 0,
            "Expected screen to be called, but it was not called."
        );

        if ($name !== null || $properties !== null) {
            $matching = $this->filterCalls($this->screenCalls, compact('name', 'properties'));

            PHPUnit::assertNotEmpty(
                $matching,
                "Expected screen to be called with matching parameters, but no matching calls were found."
            );
        }
    }

    /**
     * Assert that group was called.
     */
    public function assertGrouped(string $groupId, ?array $traits = null, ?string $userId = null): void
    {
        $matching = array_filter($this->groupCalls, function ($call) use ($groupId, $traits, $userId) {
            if ($call['groupId'] !== $groupId) {
                return false;
            }

            if ($traits !== null && $call['traits'] !== $traits) {
                return false;
            }

            if ($userId !== null && $call['userId'] !== $userId) {
                return false;
            }

            return true;
        });

        PHPUnit::assertNotEmpty(
            $matching,
            "Expected group '{$groupId}' to be called, but it was not found."
        );
    }

    /**
     * Assert the number of times identify was called.
     */
    public function assertIdentifyCount(int $count): void
    {
        PHPUnit::assertCount(
            $count,
            $this->identifyCalls,
            "Expected identify to be called {$count} time(s), but it was called " . count($this->identifyCalls) . " time(s)."
        );
    }

    /**
     * Assert the number of times track was called.
     */
    public function assertTrackCount(int $count): void
    {
        PHPUnit::assertCount(
            $count,
            $this->trackCalls,
            "Expected track to be called {$count} time(s), but it was called " . count($this->trackCalls) . " time(s)."
        );
    }

    /**
     * Assert that nothing was called.
     */
    public function assertNothingCalled(): void
    {
        $totalCalls = count($this->identifyCalls)
            + count($this->trackCalls)
            + count($this->pageCalls)
            + count($this->screenCalls)
            + count($this->groupCalls)
            + count($this->batchCalls);

        PHPUnit::assertEquals(
            0,
            $totalCalls,
            "Expected no Dittofeed calls, but {$totalCalls} call(s) were made."
        );
    }

    /**
     * Get all identify calls.
     */
    public function identifyCalls(): array
    {
        return $this->identifyCalls;
    }

    /**
     * Get all track calls.
     */
    public function trackCalls(): array
    {
        return $this->trackCalls;
    }

    /**
     * Get all page calls.
     */
    public function pageCalls(): array
    {
        return $this->pageCalls;
    }

    /**
     * Get all screen calls.
     */
    public function screenCalls(): array
    {
        return $this->screenCalls;
    }

    /**
     * Get all group calls.
     */
    public function groupCalls(): array
    {
        return $this->groupCalls;
    }

    /**
     * Get all batch calls.
     */
    public function batchCalls(): array
    {
        return $this->batchCalls;
    }

    /**
     * Filter calls by criteria.
     */
    protected function filterCalls(array $calls, array $criteria): array
    {
        return array_filter($calls, function ($call) use ($criteria) {
            foreach ($criteria as $key => $value) {
                if ($value !== null && (!isset($call[$key]) || $call[$key] !== $value)) {
                    return false;
                }
            }

            return true;
        });
    }
}
