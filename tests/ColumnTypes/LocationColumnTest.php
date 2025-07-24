<?php

namespace MondayV2SDK\Tests\ColumnTypes;

use PHPUnit\Framework\TestCase;
use MondayV2SDK\ColumnTypes\LocationColumn;

class LocationColumnTest extends TestCase
{
    public function testConstructorWithString()
    {
        $column = new LocationColumn('location_01', '123 Main St, New York, NY');
        
        $this->assertEquals('location', $column->getType());
        $this->assertEquals('location_01', $column->getColumnId());
        $this->assertEquals('123 Main St, New York, NY', $column->getAddress());
        $this->assertNull($column->getCity());
        $this->assertNull($column->getState());
        $this->assertNull($column->getCountry());
    }

    public function testConstructorWithArray()
    {
        $locationData = [
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'country_code' => 'US'
        ];
        
        $column = new LocationColumn('location_01', $locationData);
        
        $this->assertEquals('location', $column->getType());
        $this->assertEquals('location_01', $column->getColumnId());
        $this->assertEquals('123 Main St', $column->getAddress());
        $this->assertEquals('New York', $column->getCity());
        $this->assertEquals('NY', $column->getState());
        $this->assertEquals('USA', $column->getCountry());
        $this->assertEquals(40.7128, $column->getLatitude());
        $this->assertEquals(-74.0060, $column->getLongitude());
        $this->assertEquals('US', $column->getCountryCode());
    }

    public function testConstructorWithInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Location must be a string or array');
        
        new LocationColumn('location_01', 123);
    }

    public function testWithFullAddress()
    {
        $column = LocationColumn::withFullAddress(
            'location_01',
            '123 Main St',
            'New York',
            'NY',
            'USA',
            40.7128,
            -74.0060,
            'US'
        );
        
        $this->assertEquals('location', $column->getType());
        $this->assertEquals('123 Main St', $column->getAddress());
        $this->assertEquals('New York', $column->getCity());
        $this->assertEquals('NY', $column->getState());
        $this->assertEquals('USA', $column->getCountry());
        $this->assertEquals(40.7128, $column->getLatitude());
        $this->assertEquals(-74.0060, $column->getLongitude());
        $this->assertEquals('US', $column->getCountryCode());
    }

    public function testWithCoordinates()
    {
        $column = LocationColumn::withCoordinates('location_01', 40.7128, -74.0060, '123 Main St');
        
        $this->assertEquals('location', $column->getType());
        $this->assertEquals('123 Main St', $column->getAddress());
        $this->assertNull($column->getCity());
        $this->assertNull($column->getState());
        $this->assertNull($column->getCountry());
        $this->assertEquals(40.7128, $column->getLatitude());
        $this->assertEquals(-74.0060, $column->getLongitude());
    }

    public function testWithCityState()
    {
        $column = LocationColumn::withCityState('location_01', 'New York', 'NY', 'USA');
        
        $this->assertEquals('location', $column->getType());
        $this->assertNull($column->getAddress());
        $this->assertEquals('New York', $column->getCity());
        $this->assertEquals('NY', $column->getState());
        $this->assertEquals('USA', $column->getCountry());
        $this->assertNull($column->getLatitude());
        $this->assertNull($column->getLongitude());
    }

    public function testWithAddress()
    {
        $column = LocationColumn::withAddress('location_01', '123 Main St, New York, NY');
        
        $this->assertEquals('location', $column->getType());
        $this->assertEquals('123 Main St, New York, NY', $column->getAddress());
        $this->assertNull($column->getCity());
        $this->assertNull($column->getState());
        $this->assertNull($column->getCountry());
    }

    public function testEmpty()
    {
        $column = LocationColumn::empty('location_01');
        
        $this->assertEquals('location', $column->getType());
        $this->assertEquals('', $column->getAddress());
        $this->assertNull($column->getCity());
        $this->assertNull($column->getState());
        $this->assertNull($column->getCountry());
    }

    public function testGetFormattedAddress()
    {
        $column = LocationColumn::withFullAddress(
            'location_01',
            '123 Main St',
            'New York',
            'NY',
            'USA'
        );
        
        $this->assertEquals('123 Main St, New York, NY, USA', $column->getFormattedAddress());
    }

    public function testGetFormattedAddressWithPartialData()
    {
        $column = LocationColumn::withCityState('location_01', 'New York', 'NY');
        
        $this->assertEquals('New York, NY', $column->getFormattedAddress());
    }

    public function testGetValue()
    {
        $column = LocationColumn::withFullAddress(
            'location_01',
            '123 Main St',
            'New York',
            'NY',
            'USA',
            40.7128,
            -74.0060,
            'US'
        );
        
        $expected = [
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'lat' => 40.7128,
            'lng' => -74.0060,
            'country_code' => 'US'
        ];
        
        $this->assertEquals($expected, $column->getValue());
    }

    public function testValidateWithValidData()
    {
        $column = LocationColumn::withFullAddress(
            'location_01',
            '123 Main St',
            'New York',
            'NY',
            'USA'
        );
        
        // validate() now returns void, so we just check it doesn't throw an exception
        $this->expectNotToPerformAssertions();
        $column->validate();
    }

    public function testValidateWithEmptyData()
    {
        $column = LocationColumn::empty('location_01');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one location field must be provided');
        
        $column->validate();
    }

    public function testValidateWithInvalidLatitude()
    {
        $column = LocationColumn::withCoordinates('location_01', 100.0, -74.0060, null, true);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid latitude: 100');
        
        $column->validate();
    }

    public function testValidateWithInvalidLongitude()
    {
        $column = LocationColumn::withCoordinates('location_01', 40.7128, 200.0, null, true);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid longitude: 200');
        
        $column->validate();
    }

    public function testValidateWithInvalidCountryCode()
    {
        $column = new LocationColumn(
            'location_01', [
            'address' => '123 Main St',
            'country_code' => 'INVALID'
            ], true
        );
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid country code: INVALID');
        
        $column->validate();
    }

    public function testValidateWithValidCountryCode()
    {
        $column = new LocationColumn(
            'location_01', [
            'address' => '123 Main St',
            'country_code' => 'US'
            ]
        );
        
        // validate() now returns void, so we just check it doesn't throw an exception
        $this->expectNotToPerformAssertions();
        $column->validate();
    }

    public function testStringCoordinatesConversion()
    {
        $column = new LocationColumn(
            'location_01', [
            'address' => '123 Main St',
            'lat' => '40.7128',
            'lng' => '-74.0060'
            ]
        );
        
        $this->assertEquals(40.7128, $column->getLatitude());
        $this->assertEquals(-74.0060, $column->getLongitude());
    }

    public function testGetValueWithPartialData()
    {
        $column = LocationColumn::withCityState('location_01', 'New York', 'NY');
        
        $expected = [
            'city' => 'New York',
            'state' => 'NY'
        ];
        
        $this->assertEquals($expected, $column->getValue());
    }

    public function testGetValueWithOnlyAddress()
    {
        $column = LocationColumn::withAddress('location_01', '123 Main St');
        
        $expected = [
            'address' => '123 Main St'
        ];
        
        $this->assertEquals($expected, $column->getValue());
    }
} 