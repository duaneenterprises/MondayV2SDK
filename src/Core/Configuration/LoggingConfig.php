<?php

namespace MondayV2SDK\Core\Configuration;

/**
 * Logging configuration
 *
 * Encapsulates all logging settings with proper validation
 * and default values.
 */
class LoggingConfig
{
    public const DEFAULT_LEVEL = 'info';
    public const DEFAULT_ENABLED = false;

    private bool $enabled;
    private string $level;
    private ?string $file;

    public function __construct(
        bool $enabled = self::DEFAULT_ENABLED,
        string $level = self::DEFAULT_LEVEL,
        ?string $file = null
    ) {
        $this->enabled = $enabled;
        $this->level = $level;
        $this->file = $file;
        $this->validate();
    }

    /**
     * Create from array configuration
     *
     * @param  array<string, mixed> $config
     * @return self
     */
    public static function fromArray(array $config): self
    {
        return new self(
            enabled: $config['enabled'] ?? self::DEFAULT_ENABLED,
            level: $config['level'] ?? self::DEFAULT_LEVEL,
            file: $config['file'] ?? null
        );
    }

    /**
     * Convert to array for backward compatibility
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'level' => $this->level,
            'file' => $this->file,
        ];
    }

    /**
     * Get enabled status
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get log level
     *
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * Get log file path
     *
     * @return string|null
     */
    public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * Validate configuration values
     *
     * @throws \InvalidArgumentException
     */
    private function validate(): void
    {
        $validLevels = ['debug', 'info', 'warning', 'error', 'critical'];

        if (!in_array($this->level, $validLevels)) {
            throw new \InvalidArgumentException(
                'Log level must be one of: ' . implode(', ', $validLevels)
            );
        }

        // File validation is handled by type system - no additional validation needed
    }
}
