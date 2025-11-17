<?php

namespace Dittofeed\Laravel\Tests;

use Dittofeed\Laravel\DittofeedServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            DittofeedServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Dittofeed' => \Dittofeed\Laravel\Facades\Dittofeed::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('dittofeed.write_key', 'test-write-key');
        config()->set('dittofeed.host', 'https://test.dittofeed.com');
        config()->set('dittofeed.testing', true);
    }
}
