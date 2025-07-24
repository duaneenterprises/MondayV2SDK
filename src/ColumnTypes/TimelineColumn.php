<?php

namespace MondayV2SDK\ColumnTypes;

/**
 * Timeline column type for Monday.com
 * 
 * Handles timeline/date range columns with proper validation and formatting.
 * Supports both date strings and DateTime objects.
 */
class TimelineColumn extends AbstractColumnType
{
    private string $startDate;
    private string $endDate;

    /**
     * Constructor
     * 
     * @param string $columnId  The column ID
     * @param string $startDate The start date (YYYY-MM-DD format)
     * @param string $endDate   The end date (YYYY-MM-DD format)
     */
    public function __construct(string $columnId, string $startDate, string $endDate)
    {
        $this->startDate = $this->formatDate($startDate);
        $this->endDate = $this->formatDate($endDate);
        
        parent::__construct(
            $columnId, [
            'date' => $this->startDate,
            'end_date' => $this->endDate
            ]
        );
    }

    /**
     * Get the column type identifier
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'timeline';
    }

    /**
     * Validate the timeline value
     * 
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        parent::validate();

        if (!empty($this->startDate) && !$this->isValidDate($this->startDate)) {
            throw new \InvalidArgumentException("Invalid start date: {$this->startDate}");
        }

        if (!empty($this->endDate) && !$this->isValidDate($this->endDate)) {
            throw new \InvalidArgumentException("Invalid end date: {$this->endDate}");
        }

        if (!empty($this->startDate) && !empty($this->endDate) && strtotime($this->startDate) > strtotime($this->endDate)) {
            throw new \InvalidArgumentException('Start date cannot be after end date');
        }
    }

    /**
     * Get the column value for API
     * 
     * @return array<string, string>
     */
    public function getValue(): array
    {
        return [
            'date' => $this->startDate,
            'end_date' => $this->endDate
        ];
    }

    /**
     * Create a timeline column from DateTime objects
     * 
     * @param  string    $columnId  The column ID
     * @param  \DateTime $startDate The start date
     * @param  \DateTime $endDate   The end date
     * @return self
     */
    public static function fromDateTime(string $columnId, \DateTime $startDate, \DateTime $endDate): self
    {
        return new self($columnId, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
    }

    /**
     * Create a timeline column from timestamps
     * 
     * @param  string $columnId       The column ID
     * @param  int    $startTimestamp The start timestamp
     * @param  int    $endTimestamp   The end timestamp
     * @return self
     */
    public static function fromTimestamp(string $columnId, int $startTimestamp, int $endTimestamp): self
    {
        return new self($columnId, date('Y-m-d', $startTimestamp), date('Y-m-d', $endTimestamp));
    }

    /**
     * Create an empty timeline column
     * 
     * @param  string $columnId The column ID
     * @return self
     */
    public static function empty(string $columnId): self
    {
        return new self($columnId, '', '');
    }

    /**
     * Get the start date
     * 
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * Get the end date
     * 
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->endDate;
    }

    /**
     * Get the duration in days
     * 
     * @return int
     */
    public function getDurationInDays(): int
    {
        $start = new \DateTime($this->startDate);
        $end = new \DateTime($this->endDate);
        $interval = $start->diff($end);
        return $interval->days + 1; // Include both start and end dates
    }

    /**
     * Format a date value
     * 
     * @param  string $date The date to format
     * @return string Formatted date
     */
    private function formatDate(string $date): string
    {
        if (empty($date)) {
            return '';
        }

        // If it's already in YYYY-MM-DD format, return as is
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // Try to parse and format the date
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            throw new \InvalidArgumentException("Invalid date format: {$date}");
        }

        return date('Y-m-d', $timestamp);
    }

    /**
     * Validate date format
     * 
     * @param  string $date
     * @return bool
     */
    private function isValidDate(string $date): bool
    {
        if (empty($date)) {
            return false;
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return false;
        }

        // Check if the formatted date matches the input (handles edge cases)
        return date('Y-m-d', $timestamp) === $date;
    }
} 