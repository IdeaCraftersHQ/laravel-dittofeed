<?php

namespace Dittofeed\Laravel;

use Dittofeed\Laravel\Commands\DittofeedFlushCommand;
use Dittofeed\Laravel\Commands\DittofeedStatsCommand;
use Dittofeed\Laravel\Commands\DittofeedTestCommand;
use Dittofeed\Laravel\Listeners\TrackLogin;
use Dittofeed\Laravel\Listeners\TrackLogout;
use Dittofeed\Laravel\Listeners\TrackRegistration;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class DittofeedServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/dittofeed.php',
            'dittofeed'
        );

        $this->app->singleton('dittofeed', function ($app) {
            return new DittofeedManager(config('dittofeed'));
        });

        $this->app->alias('dittofeed', DittofeedManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/dittofeed.php' => config_path('dittofeed.php'),
            ], 'dittofeed-config');

            // Register commands
            $this->commands([
                DittofeedTestCommand::class,
                DittofeedFlushCommand::class,
                DittofeedStatsCommand::class,
            ]);
        }

        // Register event listeners if auto-tracking is enabled
        if (config('dittofeed.auto_track.enabled') && config('dittofeed.auto_track.auth_events')) {
            $this->registerEventListeners();
        }

        // Register middleware
        $this->registerMiddleware();

        // Register shutdown handler to flush events
        $this->registerShutdownHandler();
    }

    /**
     * Register event listeners for automatic tracking.
     */
    protected function registerEventListeners(): void
    {
        Event::listen(Registered::class, TrackRegistration::class);
        Event::listen(Login::class, TrackLogin::class);
        Event::listen(Logout::class, TrackLogout::class);
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        if (config('dittofeed.auto_track.enabled') && config('dittofeed.auto_track.page_views')) {
            $router = $this->app['router'];
            $router->aliasMiddleware('dittofeed.track-pages', \Dittofeed\Laravel\Middleware\TrackPageViews::class);
        }
    }

    /**
     * Register a shutdown handler to flush any queued events.
     */
    protected function registerShutdownHandler(): void
    {
        register_shutdown_function(function () {
            try {
                if ($this->app->bound('dittofeed')) {
                    $this->app->make('dittofeed')->flush();
                }
            } catch (\Exception $e) {
                // Silently catch exceptions during shutdown
            }
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return ['dittofeed', DittofeedManager::class];
    }
}
