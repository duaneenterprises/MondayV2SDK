<?php

namespace MondayV2SDK\ColumnTypes;

use MondayV2SDK\Core\InputValidator;

/**
 * Location column type for Monday.com
 * 
 * Handles location columns with address, city, state, country, and coordinates.
 * Supports both full address objects and simple string addresses.
 */
class LocationColumn extends AbstractColumnType
{
    private ?string $address;
    private ?string $city;
    private ?string $state;
    private ?string $country;
    private ?float $lat;
    private ?float $lng;
    private ?string $countryCode;

    /**
     * Constructor
     * 
     * @param string $columnId       The column ID
     * @param mixed  $location       Location data or address string
     * @param bool   $skipValidation Whether to skip validation
     */
    public function __construct(string $columnId, mixed $location, bool $skipValidation = false)
    {
        // Validate and sanitize input data (skip validation if requested)
        if ($skipValidation) {
            if (is_string($location)) {
                $this->address = $location;
                $this->city = null;
                $this->state = null;
                $this->country = null;
                $this->lat = null;
                $this->lng = null;
                $this->countryCode = null;
            } elseif (is_array($location)) {
                $this->address = $location['address'] ?? null;
                $this->city = $location['city'] ?? null;
                $this->state = $location['state'] ?? null;
                $this->country = $location['country'] ?? null;
                $this->lat = isset($location['lat']) ? (float) $location['lat'] : null;
                $this->lng = isset($location['lng']) ? (float) $location['lng'] : null;
                $this->countryCode = $location['country_code'] ?? null;
            } else {
                throw new \InvalidArgumentException('Location must be a string or array');
            }
        } else {
            $validatedLocation = InputValidator::validateLocation($location);
            
            $this->address = $validatedLocation['address'] ?? null;
            $this->city = $validatedLocation['city'] ?? null;
            $this->state = $validatedLocation['state'] ?? null;
            $this->country = $validatedLocation['country'] ?? null;
            $this->lat = $validatedLocation['lat'] ?? null;
            $this->lng = $validatedLocation['lng'] ?? null;
            $this->countryCode = $validatedLocation['country_code'] ?? null;
        }

        parent::__construct($columnId, $this->buildValue(), $skipValidation);
    }

    /**
     * Get the column type identifier
     * 
     * @return string
     */
    public function getType(): string
    {
        return 'location';
    }

    /**
     * Validate the location value
     * 
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        parent::validate();

        // Validate coordinates if provided (check these first)
        if ($this->lat !== null && !$this->isValidLatitude($this->lat)) {
            throw new \InvalidArgumentException("Invalid latitude: {$this->lat}");
        }

        if ($this->lng !== null && !$this->isValidLongitude($this->lng)) {
            throw new \InvalidArgumentException("Invalid longitude: {$this->lng}");
        }

        // Validate country code if provided
        if ($this->countryCode && !$this->isValidCountryCode($this->countryCode)) {
            throw new \InvalidArgumentException("Invalid country code: {$this->countryCode}");
        }

        // At least one location field must be provided (check this last)
        if (empty($this->address) && empty($this->city) && empty($this->state) && empty($this->country) && $this->lat === null && $this->lng === null) {
            throw new \InvalidArgumentException('At least one location field must be provided');
        }
    }

    /**
     * Get the column value for API
     * 
     * @return array<string, mixed>
     */
    public function getValue(): array
    {
        $value = [];
        
        if ($this->address) {
            $value['address'] = $this->address;
        }
        
        if ($this->city) {
            $value['city'] = $this->city;
        }
        
        if ($this->state) {
            $value['state'] = $this->state;
        }
        
        if ($this->country) {
            $value['country'] = $this->country;
        }
        
        if ($this->lat !== null) {
            $value['lat'] = $this->lat;
        }
        
        if ($this->lng !== null) {
            $value['lng'] = $this->lng;
        }
        
        if ($this->countryCode) {
            $value['country_code'] = $this->countryCode;
        }
        
        return $value;
    }

    /**
     * Create a location column with full address details
     * 
     * @param  string      $columnId    The column ID
     * @param  string      $address     The street address
     * @param  string      $city        The city
     * @param  string      $state       The state/province
     * @param  string      $country     The country
     * @param  float|null  $lat         Latitude
     * @param  float|null  $lng         Longitude
     * @param  string|null $countryCode Country code (ISO 3166-1 alpha-2)
     * @return self
     */
    public static function withFullAddress(
        string $columnId,
        string $address,
        string $city,
        string $state,
        string $country,
        ?float $lat = null,
        ?float $lng = null,
        ?string $countryCode = null
    ): self {
        return new self(
            $columnId, [
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'lat' => $lat,
            'lng' => $lng,
            'country_code' => $countryCode
            ]
        );
    }

    /**
     * Create a location column with coordinates
     * 
     * @param  string      $columnId       The column ID
     * @param  float       $lat            Latitude
     * @param  float       $lng            Longitude
     * @param  string|null $address        Optional address
     * @param  bool        $skipValidation Whether to skip validation during construction
     * @return self
     */
    public static function withCoordinates(string $columnId, float $lat, float $lng, ?string $address = null, bool $skipValidation = false): self
    {
        $location = [
            'lat' => $lat,
            'lng' => $lng
        ];
        
        if ($address) {
            $location['address'] = $address;
        }
        
        return new self($columnId, $location, $skipValidation);
    }

    /**
     * Create a location column with city and state
     * 
     * @param  string      $columnId The column ID
     * @param  string      $city     The city
     * @param  string      $state    The state/province
     * @param  string|null $country  The country
     * @return self
     */
    public static function withCityState(string $columnId, string $city, string $state, ?string $country = null): self
    {
        $location = [
            'city' => $city,
            'state' => $state
        ];
        
        if ($country) {
            $location['country'] = $country;
        }
        
        return new self($columnId, $location);
    }

    /**
     * Create a location column with just an address string
     * 
     * @param  string $columnId The column ID
     * @param  string $address  The address string
     * @return self
     */
    public static function withAddress(string $columnId, string $address): self
    {
        return new self($columnId, $address);
    }

    /**
     * Create an empty location column
     * 
     * @param  string $columnId The column ID
     * @return self
     */
    public static function empty(string $columnId): self
    {
        return new self($columnId, '', true);
    }

    /**
     * Get the address
     * 
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * Get the city
     * 
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Get the state
     * 
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * Get the country
     * 
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Get the latitude
     * 
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->lat;
    }

    /**
     * Get the longitude
     * 
     * @return float|null
     */
    public function getLongitude(): ?float
    {
        return $this->lng;
    }

    /**
     * Get the country code
     * 
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * Get formatted address string
     * 
     * @return string
     */
    public function getFormattedAddress(): string
    {
        $parts = [];
        
        if ($this->address) {
            $parts[] = $this->address;
        }
        
        if ($this->city) {
            $parts[] = $this->city;
        }
        
        if ($this->state) {
            $parts[] = $this->state;
        }
        
        if ($this->country) {
            $parts[] = $this->country;
        }
        
        return implode(', ', $parts);
    }

    /**
     * Build the value array for the parent constructor
     * 
     * @return array<string, mixed>
     */
    private function buildValue(): array
    {
        $value = [];
        
        if ($this->address) {
            $value['address'] = $this->address;
        }
        
        if ($this->city) {
            $value['city'] = $this->city;
        }
        
        if ($this->state) {
            $value['state'] = $this->state;
        }
        
        if ($this->country) {
            $value['country'] = $this->country;
        }
        
        if ($this->lat !== null) {
            $value['lat'] = $this->lat;
        }
        
        if ($this->lng !== null) {
            $value['lng'] = $this->lng;
        }
        
        if ($this->countryCode) {
            $value['country_code'] = $this->countryCode;
        }
        
        return $value;
    }

    /**
     * Validate latitude
     * 
     * @param  float $lat
     * @return bool
     */
    private function isValidLatitude(float $lat): bool
    {
        return $lat >= -90 && $lat <= 90;
    }

    /**
     * Validate longitude
     * 
     * @param  float $lng
     * @return bool
     */
    private function isValidLongitude(float $lng): bool
    {
        return $lng >= -180 && $lng <= 180;
    }

    /**
     * Validate country code (ISO 3166-1 alpha-2)
     * 
     * @param  string $countryCode
     * @return bool
     */
    private function isValidCountryCode(string $countryCode): bool
    {
        return preg_match('/^[A-Z]{2}$/', $countryCode) === 1;
    }
} 