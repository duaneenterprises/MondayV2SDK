<?php

namespace MondayV2SDK\Core;

/**
 * Input validation and sanitization utility
 * 
 * Provides comprehensive validation for all user-provided data
 * to ensure security and data integrity.
 */
class InputValidator
{
    /**
     * Validate and sanitize board ID
     * 
     * @param  mixed $boardId Board ID to validate
     * @return int Validated board ID
     * @throws \InvalidArgumentException
     */
    public static function validateBoardId(mixed $boardId): int
    {
        if (is_string($boardId)) {
            // Remove any whitespace and validate format
            $boardId = trim($boardId);
            if (!preg_match('/^\d+$/', $boardId)) {
                throw new \InvalidArgumentException('Board ID must be a positive integer');
            }
            $boardId = (int) $boardId;
        }

        if (!is_int($boardId) || $boardId <= 0) {
            throw new \InvalidArgumentException('Board ID must be a positive integer');
        }

        return $boardId;
    }

    /**
     * Validate and sanitize item ID
     * 
     * @param  mixed $itemId Item ID to validate
     * @return int Validated item ID
     * @throws \InvalidArgumentException
     */
    public static function validateItemId(mixed $itemId): int
    {
        if (is_string($itemId)) {
            // Remove any whitespace and validate format
            $itemId = trim($itemId);
            if (!preg_match('/^\d+$/', $itemId)) {
                throw new \InvalidArgumentException('Item ID must be a positive integer');
            }
            $itemId = (int) $itemId;
        }

        if (!is_int($itemId) || $itemId <= 0) {
            throw new \InvalidArgumentException('Item ID must be a positive integer');
        }

        return $itemId;
    }

    /**
     * Validate and sanitize item name
     * 
     * @param  mixed $itemName Item name to validate
     * @return string Validated item name
     * @throws \InvalidArgumentException
     */
    public static function validateItemName(mixed $itemName): string
    {
        if (!is_string($itemName)) {
            throw new \InvalidArgumentException('Item name must be a string');
        }

        $itemName = trim($itemName);
        
        if (empty($itemName)) {
            throw new \InvalidArgumentException('Item name cannot be empty');
        }

        if (strlen($itemName) > 255) {
            throw new \InvalidArgumentException('Item name cannot exceed 255 characters');
        }

        // Remove any potentially dangerous characters
        $itemName = preg_replace('/[<>"\']/', '', $itemName);
        
        return $itemName;
    }

    /**
     * Validate and sanitize board name
     * 
     * @param  mixed $boardName Board name to validate
     * @return string Validated board name
     * @throws \InvalidArgumentException
     */
    public static function validateBoardName(mixed $boardName): string
    {
        if (!is_string($boardName)) {
            throw new \InvalidArgumentException('Board name must be a string');
        }

        $boardName = trim($boardName);
        
        if (empty($boardName)) {
            throw new \InvalidArgumentException('Board name cannot be empty');
        }

        if (strlen($boardName) > 255) {
            throw new \InvalidArgumentException('Board name cannot exceed 255 characters');
        }

        // Remove any potentially dangerous characters
        $boardName = preg_replace('/[<>"\']/', '', $boardName);
        
        return $boardName;
    }

    /**
     * Validate and sanitize board description
     * 
     * @param  mixed $description Board description to validate
     * @return string|null Validated board description
     * @throws \InvalidArgumentException
     */
    public static function validateBoardDescription(mixed $description): ?string
    {
        if ($description === null) {
            return null;
        }

        if (!is_string($description)) {
            throw new \InvalidArgumentException('Board description must be a string or null');
        }

        $description = trim($description);
        
        if (strlen($description) > 1000) {
            throw new \InvalidArgumentException('Board description cannot exceed 1000 characters');
        }

        // Remove any potentially dangerous characters
        $description = preg_replace('/[<>"\']/', '', $description);
        
        return $description;
    }

    /**
     * Validate and sanitize column values array
     * 
     * @param  mixed $columnValues Column values to validate
     * @return array Validated column values
     * @throws \InvalidArgumentException
     */
    public static function validateColumnValues(mixed $columnValues): array
    {
        if (!is_array($columnValues)) {
            throw new \InvalidArgumentException('Column values must be an array');
        }

        // For indexed arrays (like ColumnTypeInterface objects), just return as-is
        if (array_keys($columnValues) === range(0, count($columnValues) - 1)) {
            return $columnValues;
        }

        $validatedValues = [];
        
        foreach ($columnValues as $columnId => $value) {
            if (!is_string($columnId)) {
                throw new \InvalidArgumentException('Column ID must be a string');
            }

            $columnId = trim($columnId);
            if (empty($columnId)) {
                throw new \InvalidArgumentException('Column ID cannot be empty');
            }

            // Validate column ID format (should be a valid identifier)
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $columnId)) {
                throw new \InvalidArgumentException('Invalid column ID format');
            }

            $validatedValues[$columnId] = $value;
        }

        return $validatedValues;
    }

    /**
     * Validate and sanitize cursor for pagination
     * 
     * @param  mixed $cursor Cursor to validate
     * @return string Validated cursor
     * @throws \InvalidArgumentException
     */
    public static function validateCursor(mixed $cursor): string
    {
        if (!is_string($cursor)) {
            throw new \InvalidArgumentException('Cursor must be a string');
        }

        $cursor = trim($cursor);
        
        if (empty($cursor)) {
            throw new \InvalidArgumentException('Cursor cannot be empty');
        }

        // Validate cursor format (base64 encoded string)
        if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $cursor)) {
            throw new \InvalidArgumentException('Invalid cursor format');
        }

        return $cursor;
    }

    /**
     * Validate and sanitize limit for pagination
     * 
     * @param  mixed $limit Limit to validate
     * @return int Validated limit
     * @throws \InvalidArgumentException
     */
    public static function validateLimit(mixed $limit): int
    {
        if (is_string($limit)) {
            $limit = trim($limit);
            if (!preg_match('/^\d+$/', $limit)) {
                throw new \InvalidArgumentException('Limit must be a positive integer');
            }
            $limit = (int) $limit;
        }

        if (!is_int($limit) || $limit <= 0) {
            throw new \InvalidArgumentException('Limit must be a positive integer');
        }

        // Monday.com API has a maximum limit
        if ($limit > 1000) {
            throw new \InvalidArgumentException('Limit cannot exceed 1000');
        }

        return $limit;
    }

    /**
     * Validate and sanitize options array
     * 
     * @param  mixed $options Options to validate
     * @return array Validated options
     * @throws \InvalidArgumentException
     */
    public static function validateOptions(mixed $options): array
    {
        if (!is_array($options)) {
            throw new \InvalidArgumentException('Options must be an array');
        }

        $validatedOptions = [];
        
        foreach ($options as $key => $value) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException('Option keys must be strings');
            }

            $key = trim($key);
            if (empty($key)) {
                throw new \InvalidArgumentException('Option keys cannot be empty');
            }

            // Validate option key format
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                throw new \InvalidArgumentException('Invalid option key format');
            }

            $validatedOptions[$key] = $value;
        }

        return $validatedOptions;
    }

    /**
     * Validate and sanitize email address
     * 
     * @param  mixed $email Email to validate
     * @return string Validated email
     * @throws \InvalidArgumentException
     */
    public static function validateEmail(mixed $email): string
    {
        if (!is_string($email)) {
            throw new \InvalidArgumentException('Email must be a string');
        }

        $email = trim($email);
        
        if (empty($email)) {
            throw new \InvalidArgumentException('Email cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        if (strlen($email) > 254) {
            throw new \InvalidArgumentException('Email cannot exceed 254 characters');
        }

        return $email;
    }

    /**
     * Validate and sanitize phone number
     * 
     * @param  mixed $phone Phone number to validate
     * @return string Validated phone number
     * @throws \InvalidArgumentException
     */
    public static function validatePhone(mixed $phone): string
    {
        if (!is_string($phone)) {
            throw new \InvalidArgumentException('Phone number must be a string');
        }

        $phone = trim($phone);
        
        if (empty($phone)) {
            throw new \InvalidArgumentException('Phone number cannot be empty');
        }

        // Remove all non-digit characters for validation
        $digitsOnly = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($digitsOnly) < 10) {
            throw new \InvalidArgumentException('Phone number must contain at least 10 digits');
        }

        if (strlen($phone) > 20) {
            throw new \InvalidArgumentException('Phone number cannot exceed 20 characters');
        }

        return $phone;
    }

    /**
     * Validate and sanitize location data
     * 
     * @param  mixed $location Location data to validate
     * @return array Validated location data
     * @throws \InvalidArgumentException
     */
    public static function validateLocation(mixed $location): array
    {
        if (is_string($location)) {
            $location = trim($location);
            if (empty($location)) {
                throw new \InvalidArgumentException('Location cannot be empty');
            }
            if (strlen($location) > 500) {
                throw new \InvalidArgumentException('Location string cannot exceed 500 characters');
            }
            return ['address' => $location];
        }

        if (!is_array($location)) {
            throw new \InvalidArgumentException('Location must be a string or array');
        }

        $validatedLocation = [];

        // Validate address
        if (isset($location['address'])) {
            if (!is_string($location['address'])) {
                throw new \InvalidArgumentException('Location address must be a string');
            }
            $address = trim($location['address']);
            if (strlen($address) > 500) {
                throw new \InvalidArgumentException('Location address cannot exceed 500 characters');
            }
            $validatedLocation['address'] = $address;
        }

        // Validate city
        if (isset($location['city'])) {
            if (!is_string($location['city'])) {
                throw new \InvalidArgumentException('Location city must be a string');
            }
            $city = trim($location['city']);
            if (strlen($city) > 100) {
                throw new \InvalidArgumentException('Location city cannot exceed 100 characters');
            }
            $validatedLocation['city'] = $city;
        }

        // Validate state
        if (isset($location['state'])) {
            if (!is_string($location['state'])) {
                throw new \InvalidArgumentException('Location state must be a string');
            }
            $state = trim($location['state']);
            if (strlen($state) > 100) {
                throw new \InvalidArgumentException('Location state cannot exceed 100 characters');
            }
            $validatedLocation['state'] = $state;
        }

        // Validate country
        if (isset($location['country'])) {
            if (!is_string($location['country'])) {
                throw new \InvalidArgumentException('Location country must be a string');
            }
            $country = trim($location['country']);
            if (strlen($country) > 100) {
                throw new \InvalidArgumentException('Location country cannot exceed 100 characters');
            }
            $validatedLocation['country'] = $country;
        }

        // Validate coordinates
        if (isset($location['lat'])) {
            if (!is_numeric($location['lat'])) {
                throw new \InvalidArgumentException('Latitude must be numeric');
            }
            $lat = (float) $location['lat'];
            if ($lat < -90 || $lat > 90) {
                throw new \InvalidArgumentException('Latitude must be between -90 and 90');
            }
            $validatedLocation['lat'] = $lat;
        }

        if (isset($location['lng'])) {
            if (!is_numeric($location['lng'])) {
                throw new \InvalidArgumentException('Longitude must be numeric');
            }
            $lng = (float) $location['lng'];
            if ($lng < -180 || $lng > 180) {
                throw new \InvalidArgumentException('Longitude must be between -180 and 180');
            }
            $validatedLocation['lng'] = $lng;
        }

        // Validate country code
        if (isset($location['country_code'])) {
            if (!is_string($location['country_code'])) {
                throw new \InvalidArgumentException('Country code must be a string');
            }
            $countryCode = trim(strtoupper($location['country_code']));
            if (!preg_match('/^[A-Z]{2}$/', $countryCode)) {
                throw new \InvalidArgumentException('Country code must be a 2-letter ISO code');
            }
            $validatedLocation['country_code'] = $countryCode;
        }

        // Ensure at least one field is provided
        if (empty($validatedLocation)) {
            throw new \InvalidArgumentException('Location must contain at least one field');
        }

        return $validatedLocation;
    }

    /**
     * Validate and sanitize status data
     * 
     * @param  mixed $status Status data to validate
     * @return array Validated status data
     * @throws \InvalidArgumentException
     */
    public static function validateStatus(mixed $status): array
    {
        if (is_string($status)) {
            $status = trim($status);
            if (empty($status)) {
                throw new \InvalidArgumentException('Status cannot be empty');
            }
            if (strlen($status) > 100) {
                throw new \InvalidArgumentException('Status cannot exceed 100 characters');
            }
            return ['labels' => [$status]];
        }

        if (!is_array($status)) {
            throw new \InvalidArgumentException('Status must be a string or array');
        }

        $validatedStatus = [];

        // Validate labels
        if (isset($status['labels'])) {
            if (!is_array($status['labels'])) {
                throw new \InvalidArgumentException('Status labels must be an array');
            }
            
            $validatedLabels = [];
            foreach ($status['labels'] as $label) {
                if (!is_string($label)) {
                    throw new \InvalidArgumentException('Status label must be a string');
                }
                $label = trim($label);
                if (empty($label)) {
                    throw new \InvalidArgumentException('Status label cannot be empty');
                }
                if (strlen($label) > 100) {
                    throw new \InvalidArgumentException('Status label cannot exceed 100 characters');
                }
                $validatedLabels[] = $label;
            }
            
            if (empty($validatedLabels)) {
                throw new \InvalidArgumentException('Status must contain at least one label');
            }
            
            $validatedStatus['labels'] = $validatedLabels;
        }

        // Validate color
        if (isset($status['color'])) {
            if (!is_string($status['color'])) {
                throw new \InvalidArgumentException('Status color must be a string');
            }
            $color = trim(strtolower($status['color']));
            $validColors = ['red', 'orange', 'yellow', 'green', 'blue', 'purple', 'pink', 'gray'];
            if (!in_array($color, $validColors)) {
                throw new \InvalidArgumentException('Invalid status color');
            }
            $validatedStatus['color'] = $color;
        }

        if (empty($validatedStatus)) {
            throw new \InvalidArgumentException('Status must contain labels or color');
        }

        return $validatedStatus;
    }

    /**
     * Validate and sanitize timeline data
     * 
     * @param  mixed $timeline Timeline data to validate
     * @return array Validated timeline data
     * @throws \InvalidArgumentException
     */
    public static function validateTimeline(mixed $timeline): array
    {
        if (is_string($timeline)) {
            $timeline = trim($timeline);
            if (empty($timeline)) {
                throw new \InvalidArgumentException('Timeline cannot be empty');
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $timeline)) {
                throw new \InvalidArgumentException('Timeline must be in YYYY-MM-DD format');
            }
            return ['date' => $timeline];
        }

        if (!is_array($timeline)) {
            throw new \InvalidArgumentException('Timeline must be a string or array');
        }

        $validatedTimeline = [];

        // Validate date
        if (isset($timeline['date'])) {
            if (!is_string($timeline['date'])) {
                throw new \InvalidArgumentException('Timeline date must be a string');
            }
            $date = trim($timeline['date']);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                throw new \InvalidArgumentException('Timeline date must be in YYYY-MM-DD format');
            }
            $validatedTimeline['date'] = $date;
        }

        // Validate end_date
        if (isset($timeline['end_date'])) {
            if (!is_string($timeline['end_date'])) {
                throw new \InvalidArgumentException('Timeline end_date must be a string');
            }
            $endDate = trim($timeline['end_date']);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                throw new \InvalidArgumentException('Timeline end_date must be in YYYY-MM-DD format');
            }
            $validatedTimeline['end_date'] = $endDate;
        }

        if (empty($validatedTimeline)) {
            throw new \InvalidArgumentException('Timeline must contain date or end_date');
        }

        return $validatedTimeline;
    }

    /**
     * Validate and sanitize number data
     * 
     * @param  mixed $number Number data to validate
     * @return array Validated number data
     * @throws \InvalidArgumentException
     */
    public static function validateNumber(mixed $number): array
    {
        if (is_numeric($number)) {
            return ['number' => (float) $number];
        }

        if (!is_array($number)) {
            throw new \InvalidArgumentException('Number must be numeric or an array');
        }

        $validatedNumber = [];

        // Validate number value
        if (isset($number['number'])) {
            if (!is_numeric($number['number'])) {
                throw new \InvalidArgumentException('Number value must be numeric');
            }
            $validatedNumber['number'] = (float) $number['number'];
        }

        // Validate format
        if (isset($number['format'])) {
            if (!is_string($number['format'])) {
                throw new \InvalidArgumentException('Number format must be a string');
            }
            $format = trim($number['format']);
            $validFormats = ['number', 'currency', 'percentage'];
            if (!in_array($format, $validFormats)) {
                throw new \InvalidArgumentException('Invalid number format');
            }
            $validatedNumber['format'] = $format;
        }

        if (empty($validatedNumber)) {
            throw new \InvalidArgumentException('Number must contain a numeric value');
        }

        return $validatedNumber;
    }
} 