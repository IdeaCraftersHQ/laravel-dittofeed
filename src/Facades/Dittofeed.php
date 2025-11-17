<?php

namespace Dittofeed\Laravel\Facades;

use Dittofeed\Laravel\AdminClient;
use Dittofeed\Laravel\DittofeedClient;
use Dittofeed\Laravel\Testing\FakeDittofeed;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array identify(?string $userId = null, array $traits = [], ?string $anonymousId = null)
 * @method static array track(string $event, array $properties = [], ?string $userId = null, ?string $anonymousId = null)
 * @method static array page(?string $name = null, array $properties = [], ?string $userId = null, ?string $anonymousId = null)
 * @method static array screen(?string $name = null, array $properties = [], ?string $userId = null, ?string $anonymousId = null)
 * @method static array group(string $groupId, array $traits = [], ?string $userId = null, ?string $anonymousId = null)
 * @method static array batch(array $events)
 * @method static void flush()
 * @method static AdminClient admin()
 * @method static DittofeedClient getClient()
 * @method static \Dittofeed\Laravel\DittofeedManager resolveUserIdUsing(callable $callback)
 *
 * @see \Dittofeed\Laravel\DittofeedManager
 */
class Dittofeed extends Facade
{
    /**
     * Replace the bound instance with a fake.
     */
    public static function fake(): FakeDittofeed
    {
        static::swap($fake = new FakeDittofeed());

        return $fake;
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'dittofeed';
    }
}
