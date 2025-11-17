# Dittofeed Laravel SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dittofeed/laravel.svg?style=flat-square)](https://packagist.org/packages/dittofeed/laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/dittofeed/laravel.svg?style=flat-square)](https://packagist.org/packages/dittofeed/laravel)
[![License](https://img.shields.io/packagist/l/dittofeed/laravel.svg?style=flat-square)](https://packagist.org/packages/dittofeed/laravel)

Laravel SDK for [Dittofeed](https://dittofeed.com) - an open-source customer engagement platform. This package provides a seamless integration with Laravel, enabling you to easily track user events, send targeted emails, and build automated customer journeys.

## Features

- 🚀 **Easy Integration** - Install and configure in minutes
- 🎯 **Event Tracking** - Track user actions, page views, and custom events
- 👤 **User Identification** - Associate users with traits and attributes
- 🔄 **Queue Support** - Async event processing via Laravel's queue system
- 🎨 **Model Traits** - Automatic event tracking for Eloquent models
- 🌐 **Middleware** - Automatic page view tracking
- 🎭 **Facade Support** - Clean, expressive API using Laravel facades
- 🧪 **Testing Utilities** - Fake implementation for testing
- 📊 **Admin API** - Manage templates, segments, and journeys
- 🔐 **Secure** - Built-in authentication and SSL support

## Requirements

- PHP 8.0 or higher
- Laravel 9.0, 10.0, or 11.0

## Installation

Install the package via Composer:

```bash
composer require dittofeed/laravel
```

The package will automatically register itself via Laravel's package auto-discovery.

### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=dittofeed-config
```

Add your Dittofeed credentials to your `.env` file:

```env
DITTOFEED_WRITE_KEY=your-write-key
DITTOFEED_HOST=https://app.dittofeed.com
DITTOFEED_ADMIN_KEY=your-admin-key # Optional, for admin operations
DITTOFEED_WORKSPACE_ID=your-workspace-id # Optional, for admin operations
```

## Quick Start

### Basic Event Tracking

```php
use Dittofeed\Laravel\Facades\Dittofeed;

// Identify a user
Dittofeed::identify('user-123', [
    'email' => 'john@example.com',
    'name' => 'John Doe',
    'plan' => 'premium',
]);

// Track an event
Dittofeed::track('Purchase Complete', [
    'amount' => 99.99,
    'currency' => 'USD',
    'product' => 'Premium Plan',
], 'user-123');

// Track a page view
Dittofeed::page('Pricing Page', [
    'url' => 'https://example.com/pricing',
    'title' => 'Pricing - Example App',
], 'user-123');
```

### Automatic User Tracking

The SDK automatically resolves the current authenticated user:

```php
// If user is authenticated, userId is resolved automatically
Dittofeed::track('Button Clicked', [
    'button' => 'Sign Up',
]);
```

## Usage Guide

### Event Tracking

#### Identify Users

Associate a user with their traits:

```php
Dittofeed::identify('user-123', [
    'email' => 'john@example.com',
    'name' => 'John Doe',
    'plan' => 'premium',
    'created_at' => now()->toIso8601String(),
]);
```

#### Track Events

Track custom events with properties:

```php
Dittofeed::track('Video Watched', [
    'video_id' => 'abc123',
    'title' => 'Getting Started',
    'duration' => 120,
    'progress' => 0.85,
]);
```

#### Track Page Views

Track page views (automatically includes URL, referrer, etc.):

```php
Dittofeed::page('Product Page', [
    'product_id' => 'prod-123',
    'category' => 'Electronics',
]);
```

#### Track Screen Views

For mobile applications:

```php
Dittofeed::screen('Home Screen', [
    'screen_id' => 'home',
]);
```

#### Group Users

Associate users with groups or organizations:

```php
Dittofeed::group('company-abc', [
    'name' => 'Acme Corporation',
    'plan' => 'enterprise',
    'employees' => 500,
]);
```

### Model Integration

Add the `TracksDittofeedEvents` trait to your models for automatic event tracking:

```php
use Dittofeed\Laravel\Traits\TracksDittofeedEvents;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use TracksDittofeedEvents;

    // Define which model events to track
    protected $dittofeedEvents = [
        'created' => 'User Registered',
        'updated' => 'Profile Updated',
        'deleted' => 'Account Deleted',
    ];

    // Define which attributes to send as traits
    protected $dittofeedTraits = ['email', 'name', 'plan'];
}
```

Now model events are automatically tracked:

```php
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);
// Automatically tracks "User Registered" event

$user->update(['plan' => 'premium']);
// Automatically tracks "Profile Updated" event
```

### Automatic Page View Tracking

Enable automatic page view tracking by adding the middleware to your `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \Dittofeed\Laravel\Middleware\TrackPageViews::class,
    ],
];
```

Or register it as a route middleware alias:

```php
protected $routeMiddleware = [
    'dittofeed.track-pages' => \Dittofeed\Laravel\Middleware\TrackPageViews::class,
];
```

Then use it on specific routes:

```php
Route::middleware(['dittofeed.track-pages'])->group(function () {
    Route::get('/pricing', [PricingController::class, 'index']);
    Route::get('/features', [FeaturesController::class, 'index']);
});
```

### Automatic Authentication Event Tracking

The SDK automatically tracks user registration, login, and logout events when `auto_track.auth_events` is enabled in the config (enabled by default).

Events tracked:
- `User Registered` - When a user registers
- `User Logged In` - When a user logs in
- `User Logged Out` - When a user logs out

### Queue Support

Enable queue support for asynchronous event processing:

```env
DITTOFEED_QUEUE_ENABLED=true
DITTOFEED_QUEUE_NAME=default
DITTOFEED_QUEUE_CONNECTION=redis
```

Events will be dispatched to your queue and processed asynchronously.

### Batch Operations

Send multiple events in a single request:

```php
Dittofeed::batch([
    [
        'type' => 'identify',
        'userId' => 'user-123',
        'traits' => ['email' => 'john@example.com'],
    ],
    [
        'type' => 'track',
        'userId' => 'user-123',
        'event' => 'Purchase Complete',
        'properties' => ['amount' => 99.99],
    ],
]);
```

### Admin API

Access the Admin API for managing templates, segments, and journeys:

```php
// Get all templates
$templates = Dittofeed::admin()->getTemplates();

// Create a new segment
$segment = Dittofeed::admin()->createSegment([
    'name' => 'Premium Users',
    'definition' => [
        'type' => 'trait',
        'key' => 'plan',
        'operator' => 'equals',
        'value' => 'premium',
    ],
]);

// Send a broadcast
$broadcast = Dittofeed::admin()->sendBroadcast([
    'name' => 'Product Launch',
    'segmentId' => 'segment-123',
    'templateId' => 'template-456',
]);
```

## Testing

Use the fake implementation in your tests:

```php
use Dittofeed\Laravel\Facades\Dittofeed;

public function test_user_registration_tracks_event()
{
    Dittofeed::fake();

    // Perform action that tracks events
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    // Assert events were tracked
    Dittofeed::assertIdentified('1', ['email' => 'john@example.com']);
    Dittofeed::assertTracked('User Registered');
    Dittofeed::assertTrackCount(1);
}
```

### Available Assertions

```php
Dittofeed::assertIdentified($userId, $traits);
Dittofeed::assertNotIdentified();
Dittofeed::assertTracked($event, $properties, $userId);
Dittofeed::assertNotTracked($event);
Dittofeed::assertPageViewed($name, $properties);
Dittofeed::assertScreenViewed($name, $properties);
Dittofeed::assertGrouped($groupId, $traits, $userId);
Dittofeed::assertIdentifyCount($count);
Dittofeed::assertTrackCount($count);
Dittofeed::assertNothingCalled();
```

## Artisan Commands

### Test Your Integration

```bash
php artisan dittofeed:test
```

Sends test events to verify your integration is working correctly.

### Flush Event Queue

```bash
php artisan dittofeed:flush
```

Manually flush any queued events.

### View Configuration

```bash
php artisan dittofeed:stats
```

Display your current Dittofeed configuration and status.

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DITTOFEED_WRITE_KEY` | Your write key for the Public API | - |
| `DITTOFEED_ADMIN_KEY` | Your admin key for the Admin API | - |
| `DITTOFEED_HOST` | Dittofeed host URL | `https://app.dittofeed.com` |
| `DITTOFEED_WORKSPACE_ID` | Your workspace ID | - |
| `DITTOFEED_QUEUE_ENABLED` | Enable queue support | `false` |
| `DITTOFEED_QUEUE_NAME` | Queue name | `default` |
| `DITTOFEED_QUEUE_CONNECTION` | Queue connection | `null` |
| `DITTOFEED_TIMEOUT` | HTTP timeout in seconds | `30` |
| `DITTOFEED_DEBUG` | Enable debug logging | `false` |
| `DITTOFEED_TESTING` | Enable testing mode (no events sent) | `false` |

### Auto-Tracking Configuration

```php
'auto_track' => [
    'enabled' => true,
    'page_views' => true,     // Track page views automatically
    'auth_events' => true,    // Track login/logout/registration
    'model_events' => false,  // Track model events (opt-in)
],
```

### Context Enrichment

Automatically enrich events with contextual data:

```php
'context' => [
    'enabled' => true,
    'ip' => true,           // Include IP address
    'user_agent' => true,   // Include user agent
    'timezone' => true,     // Include timezone
    'locale' => true,       // Include locale
],
```

## Advanced Usage

### Custom User ID Resolver

Define a custom resolver for the user ID:

```php
Dittofeed::resolveUserIdUsing(function () {
    return Auth::user()?->uuid;
});
```

Or set it in the config:

```php
'user_id_resolver' => fn() => Auth::user()?->uuid,
```

### Custom Event Properties in Models

Define custom properties for model events:

```php
class Order extends Model
{
    use TracksDittofeedEvents;

    protected function getDittofeedProperties(): array
    {
        return [
            'order_id' => $this->id,
            'total' => $this->total,
            'status' => $this->status,
            'items_count' => $this->items->count(),
        ];
    }
}
```

### Manual Custom Events in Models

```php
$order->trackCustomEvent('Order Shipped', [
    'tracking_number' => '123ABC',
    'carrier' => 'UPS',
]);
```

## Best Practices

1. **Use Queues** - Enable queue support for better performance
2. **Be Selective** - Only track events that provide value
3. **Name Consistently** - Use consistent event naming (e.g., "Object Action")
4. **Include Context** - Add relevant properties to events
5. **Test Thoroughly** - Use the fake implementation in tests
6. **Secure Keys** - Never commit API keys to version control
7. **Monitor Errors** - Enable debug mode during development

## Security

- Never expose your API keys in client-side code
- Use environment variables for configuration
- Enable SSL verification in production
- Review debug logs for sensitive data

## Troubleshooting

### Events Not Appearing

1. Check your write key is correct
2. Verify your host URL is correct
3. Enable debug mode to see API responses
4. Run `php artisan dittofeed:test` to test your integration

### Queue Jobs Failing

1. Ensure your queue worker is running
2. Check queue logs for errors
3. Verify network connectivity to Dittofeed
4. Increase retry attempts if needed

### Performance Issues

1. Enable queue support for async processing
2. Use batch operations for multiple events
3. Disable unnecessary auto-tracking
4. Optimize model event tracking

## Support

- **Documentation**: [https://docs.dittofeed.com](https://docs.dittofeed.com)
- **Issues**: [GitHub Issues](https://github.com/dittofeed/laravel/issues)
- **Community**: [Dittofeed Discord](https://discord.gg/dittofeed)

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

## Credits

- [Dittofeed Team](https://github.com/dittofeed)
- [All Contributors](https://github.com/dittofeed/laravel/contributors)

## Related Packages

- [dittofeed/php](https://github.com/dittofeed/php) - PHP SDK for Dittofeed
- [dittofeed/node](https://github.com/dittofeed/node) - Node.js SDK for Dittofeed
