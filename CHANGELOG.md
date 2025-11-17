# Changelog

All notable changes to `dittofeed-laravel` will be documented in this file.

## [Unreleased]

### Added
- Initial release
- Core event tracking (identify, track, page, screen, group)
- Laravel service provider with auto-discovery
- Facade support for clean API
- Configuration file with environment variables
- Queue support for asynchronous event processing
- Model trait for automatic event tracking
- Middleware for automatic page view tracking
- Event listeners for authentication events
- Admin API client for managing templates, segments, and journeys
- Artisan commands for testing and management
- Testing utilities with fake implementation
- Comprehensive documentation
- Unit and integration tests
- PSR-12 coding standards

### Features
- Automatic user ID resolution from Auth
- Context enrichment (IP, user agent, timezone, locale)
- Batch event processing
- Retry mechanism with exponential backoff
- Debug mode for development
- Testing mode for safe testing
- Anonymous ID tracking for unauthenticated users
- Custom user ID resolver support
- Excluded routes and URLs configuration

## [1.0.0] - 2024-01-15

### Added
- Initial stable release

[Unreleased]: https://github.com/dittofeed/laravel/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/dittofeed/laravel/releases/tag/v1.0.0
