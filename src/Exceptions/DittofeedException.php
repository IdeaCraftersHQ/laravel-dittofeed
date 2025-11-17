<?php

namespace Ideacrafters\Dittofeed\Exceptions;

use Exception;

class DittofeedException extends Exception
{
    /**
     * Create a new exception instance for API errors.
     */
    public static function apiError(string $message, int $statusCode = 0, ?Exception $previous = null): self
    {
        return new self("Dittofeed API Error: {$message}", $statusCode, $previous);
    }

    /**
     * Create a new exception instance for configuration errors.
     */
    public static function configurationError(string $message): self
    {
        return new self("Dittofeed Configuration Error: {$message}");
    }

    /**
     * Create a new exception instance for validation errors.
     */
    public static function validationError(string $message): self
    {
        return new self("Dittofeed Validation Error: {$message}");
    }

    /**
     * Create a new exception instance for network errors.
     */
    public static function networkError(string $message, ?Exception $previous = null): self
    {
        return new self("Dittofeed Network Error: {$message}", 0, $previous);
    }

    /**
     * Create a new exception instance for authentication errors.
     */
    public static function authenticationError(string $message = 'Invalid or missing API key'): self
    {
        return new self("Dittofeed Authentication Error: {$message}", 401);
    }

    /**
     * Create a new exception instance for rate limit errors.
     */
    public static function rateLimitError(string $message = 'Rate limit exceeded'): self
    {
        return new self("Dittofeed Rate Limit Error: {$message}", 429);
    }
}
