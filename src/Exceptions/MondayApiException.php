<?php

namespace MondayV2SDK\Exceptions;

/**
 * Exception thrown when Monday.com API returns an error
 * 
 * This exception is thrown when the Monday.com API returns an error response,
 * including GraphQL errors, HTTP errors, and other API-related issues.
 */
class MondayApiException extends \Exception
{
    /**
     * @var array<string, mixed> 
     */
    private array $context;

    /**
     * Constructor
     * 
     * @param string               $message  Error message
     * @param int                  $code     Error code
     * @param array<string, mixed> $context  Additional context data
     * @param \Throwable|null      $previous Previous exception
     */
    public function __construct(string $message = '', int $code = 0, array $context = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context data
     * 
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get GraphQL errors if any
     * 
     * @return array<int, array<string, mixed>>
     */
    public function getGraphQLErrors(): array
    {
        return $this->context['errors'] ?? [];
    }

    /**
     * Check if this is a GraphQL error
     * 
     * @return bool
     */
    public function isGraphQLError(): bool
    {
        return !empty($this->context['errors']);
    }

    /**
     * Check if this is an HTTP error
     * 
     * @return bool
     */
    public function isHttpError(): bool
    {
        return $this->code >= 400 && $this->code < 600;
    }

    /**
     * Get error details as string
     * 
     * @return string
     */
    public function getErrorDetails(): string
    {
        $details = $this->getMessage();
        
        if (!empty($this->context)) {
            $details .= ' Context: ' . json_encode($this->context);
        }
        
        return $details;
    }
} 