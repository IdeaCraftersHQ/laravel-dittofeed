<?php

namespace Ideacrafters\Dittofeed\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected array $errors = [];

    /**
     * Create a new validation exception.
     */
    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /**
     * Get validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Create a validation exception for missing required fields.
     */
    public static function missingRequired(string $field): self
    {
        return new self("The {$field} field is required.", [$field => ["The {$field} field is required."]]);
    }

    /**
     * Create a validation exception for invalid field types.
     */
    public static function invalidType(string $field, string $expectedType): self
    {
        return new self(
            "The {$field} field must be of type {$expectedType}.",
            [$field => ["The {$field} field must be of type {$expectedType}."]]
        );
    }

    /**
     * Create a validation exception for multiple errors.
     */
    public static function multiple(array $errors): self
    {
        return new self('Validation failed.', $errors);
    }
}
