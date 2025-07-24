<?php

namespace MondayV2SDK\Tests;

use PHPUnit\Framework\TestCase;
use MondayV2SDK\Response\ItemResponse;
use MondayV2SDK\Response\BoardResponse;
use MondayV2SDK\Response\PaginatedResponse;
use DateTimeImmutable;

class ResponseTest extends TestCase
{
    public function testItemResponse(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01 10:00:00');
        $updatedAt = new DateTimeImmutable('2024-01-02 15:30:00');

        $item = new ItemResponse(
            '123456789',
            'Test Item',
            'active',
            $createdAt,
            $updatedAt,
            [
                ['id' => 'text_01', 'value' => 'Sample text', 'text' => 'Sample text', 'type' => 'text']
            ]
        );

        $this->assertEquals('123456789', $item->getId());
        $this->assertEquals('Test Item', $item->getName());
        $this->assertEquals('active', $item->getState());
        $this->assertEquals($createdAt, $item->getCreatedAt());
        $this->assertEquals($updatedAt, $item->getUpdatedAt());
        $this->assertCount(1, $item->getColumnValues());
    }

    public function testItemResponseFromArray(): void
    {
        $data = [
            'id' => '123456789',
            'name' => 'Test Item',
            'state' => 'active',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-02 15:30:00',
            'column_values' => [
                ['id' => 'text_01', 'value' => 'Sample text', 'text' => 'Sample text', 'type' => 'text']
            ]
        ];

        $item = ItemResponse::fromArray($data);

        $this->assertEquals('123456789', $item->getId());
        $this->assertEquals('Test Item', $item->getName());
        $this->assertEquals('active', $item->getState());
        $this->assertEquals('2024-01-01 10:00:00', $item->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals('2024-01-02 15:30:00', $item->getUpdatedAt()->format('Y-m-d H:i:s'));
        $this->assertCount(1, $item->getColumnValues());
    }

    public function testItemResponseToArray(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01 10:00:00');
        $updatedAt = new DateTimeImmutable('2024-01-02 15:30:00');

        $item = new ItemResponse(
            '123456789',
            'Test Item',
            'active',
            $createdAt,
            $updatedAt,
            [
                ['id' => 'text_01', 'value' => 'Sample text', 'text' => 'Sample text', 'type' => 'text']
            ]
        );

        $array = $item->toArray();

        $this->assertEquals('123456789', $array['id']);
        $this->assertEquals('Test Item', $array['name']);
        $this->assertEquals('active', $array['state']);
        $this->assertEquals('2024-01-01 10:00:00', $array['created_at']);
        $this->assertEquals('2024-01-02 15:30:00', $array['updated_at']);
        $this->assertCount(1, $array['column_values']);
    }

    public function testItemResponseGetColumnValue(): void
    {
        $item = ItemResponse::fromArray(
            [
            'id' => '123456789',
            'name' => 'Test Item',
            'state' => 'active',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-02 15:30:00',
            'column_values' => [
                ['id' => 'text_01', 'value' => 'Sample text', 'text' => 'Sample text', 'type' => 'text'],
                ['id' => 'status_01', 'value' => 'Working', 'text' => 'Working', 'type' => 'status']
            ]
            ]
        );

        $textColumn = $item->getColumnValue('text_01');
        $this->assertNotNull($textColumn);
        $this->assertEquals('Sample text', $textColumn['value']);

        $statusColumn = $item->getColumnValue('status_01');
        $this->assertNotNull($statusColumn);
        $this->assertEquals('Working', $statusColumn['value']);

        $nonExistentColumn = $item->getColumnValue('non_existent');
        $this->assertNull($nonExistentColumn);
    }

    public function testItemResponseHasColumnValue(): void
    {
        $item = ItemResponse::fromArray(
            [
            'id' => '123456789',
            'name' => 'Test Item',
            'state' => 'active',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-02 15:30:00',
            'column_values' => [
                ['id' => 'text_01', 'value' => 'Sample text', 'text' => 'Sample text', 'type' => 'text']
            ]
            ]
        );

        $this->assertTrue($item->hasColumnValue('text_01'));
        $this->assertFalse($item->hasColumnValue('non_existent'));
    }

    public function testItemResponseGetColumnValuesMap(): void
    {
        $item = ItemResponse::fromArray(
            [
            'id' => '123456789',
            'name' => 'Test Item',
            'state' => 'active',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-02 15:30:00',
            'column_values' => [
                ['id' => 'text_01', 'value' => 'Sample text', 'text' => 'Sample text', 'type' => 'text'],
                ['id' => 'status_01', 'value' => 'Working', 'text' => 'Working', 'type' => 'status']
            ]
            ]
        );

        $map = $item->getColumnValuesMap();

        $this->assertArrayHasKey('text_01', $map);
        $this->assertArrayHasKey('status_01', $map);
        $this->assertEquals('Sample text', $map['text_01']['value']);
        $this->assertEquals('Working', $map['status_01']['value']);
    }

    public function testBoardResponse(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01 10:00:00');
        $updatedAt = new DateTimeImmutable('2024-01-02 15:30:00');

        $board = new BoardResponse(
            '123456789',
            'Test Board',
            'Test Description',
            'active',
            $createdAt,
            $updatedAt,
            'public',
            'folder_123',
            ['view', 'edit'],
            ['id' => 'user_123', 'name' => 'John Doe'],
            [
                ['id' => 'text_01', 'title' => 'Text Column', 'type' => 'text', 'settings_str' => '{}']
            ]
        );

        $this->assertEquals('123456789', $board->getId());
        $this->assertEquals('Test Board', $board->getName());
        $this->assertEquals('Test Description', $board->getDescription());
        $this->assertEquals('active', $board->getState());
        $this->assertEquals($createdAt, $board->getCreatedAt());
        $this->assertEquals($updatedAt, $board->getUpdatedAt());
        $this->assertEquals('public', $board->getBoardKind());
        $this->assertEquals('folder_123', $board->getBoardFolderId());
        $this->assertEquals(['view', 'edit'], $board->getPermissions());
        $this->assertEquals(['id' => 'user_123', 'name' => 'John Doe'], $board->getOwner());
        $this->assertCount(1, $board->getColumns());
    }

    public function testBoardResponseFromArray(): void
    {
        $data = [
            'id' => '123456789',
            'name' => 'Test Board',
            'description' => 'Test Description',
            'state' => 'active',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-02 15:30:00',
            'board_kind' => 'public',
            'board_folder_id' => 'folder_123',
            'permissions' => ['view', 'edit'],
            'owner' => ['id' => 'user_123', 'name' => 'John Doe'],
            'columns' => [
                ['id' => 'text_01', 'title' => 'Text Column', 'type' => 'text', 'settings_str' => '{}']
            ]
        ];

        $board = BoardResponse::fromArray($data);

        $this->assertEquals('123456789', $board->getId());
        $this->assertEquals('Test Board', $board->getName());
        $this->assertEquals('Test Description', $board->getDescription());
        $this->assertEquals('active', $board->getState());
        $this->assertEquals('2024-01-01 10:00:00', $board->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals('2024-01-02 15:30:00', $board->getUpdatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals('public', $board->getBoardKind());
        $this->assertEquals('folder_123', $board->getBoardFolderId());
        $this->assertEquals(['view', 'edit'], $board->getPermissions());
        $this->assertEquals(['id' => 'user_123', 'name' => 'John Doe'], $board->getOwner());
        $this->assertCount(1, $board->getColumns());
    }

    public function testBoardResponseGetColumn(): void
    {
        $board = BoardResponse::fromArray(
            [
            'id' => '123456789',
            'name' => 'Test Board',
            'description' => 'Test Description',
            'state' => 'active',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-02 15:30:00',
            'board_kind' => 'public',
            'board_folder_id' => 'folder_123',
            'permissions' => ['view', 'edit'],
            'owner' => ['id' => 'user_123', 'name' => 'John Doe'],
            'columns' => [
                ['id' => 'text_01', 'title' => 'Text Column', 'type' => 'text', 'settings_str' => '{}'],
                ['id' => 'status_01', 'title' => 'Status Column', 'type' => 'status', 'settings_str' => '{}']
            ]
            ]
        );

        $textColumn = $board->getColumn('text_01');
        $this->assertNotNull($textColumn);
        $this->assertEquals('Text Column', $textColumn['title']);

        $statusColumn = $board->getColumn('status_01');
        $this->assertNotNull($statusColumn);
        $this->assertEquals('Status Column', $statusColumn['title']);

        $nonExistentColumn = $board->getColumn('non_existent');
        $this->assertNull($nonExistentColumn);
    }

    public function testBoardResponseHasColumn(): void
    {
        $board = BoardResponse::fromArray(
            [
            'id' => '123456789',
            'name' => 'Test Board',
            'description' => 'Test Description',
            'state' => 'active',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-02 15:30:00',
            'board_kind' => 'public',
            'board_folder_id' => 'folder_123',
            'permissions' => ['view', 'edit'],
            'owner' => ['id' => 'user_123', 'name' => 'John Doe'],
            'columns' => [
                ['id' => 'text_01', 'title' => 'Text Column', 'type' => 'text', 'settings_str' => '{}']
            ]
            ]
        );

        $this->assertTrue($board->hasColumn('text_01'));
        $this->assertFalse($board->hasColumn('non_existent'));
    }

    public function testBoardResponseGetOwnerName(): void
    {
        $board = BoardResponse::fromArray(
            [
            'id' => '123456789',
            'name' => 'Test Board',
            'description' => 'Test Description',
            'state' => 'active',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-02 15:30:00',
            'board_kind' => 'public',
            'board_folder_id' => 'folder_123',
            'permissions' => ['view', 'edit'],
            'owner' => ['id' => 'user_123', 'name' => 'John Doe'],
            'columns' => []
            ]
        );

        $this->assertEquals('John Doe', $board->getOwnerName());
        $this->assertEquals('user_123', $board->getOwnerId());
    }

    public function testPaginatedResponse(): void
    {
        $items = [
            ['id' => '1', 'name' => 'Item 1'],
            ['id' => '2', 'name' => 'Item 2']
        ];

        $response = new PaginatedResponse('next-cursor', $items);

        $this->assertEquals('next-cursor', $response->getCursor());
        $this->assertEquals($items, $response->getItems());
        $this->assertTrue($response->hasNextPage());
        $this->assertEquals(2, $response->getCount());
        $this->assertFalse($response->isEmpty());
    }

    public function testPaginatedResponseNoNextPage(): void
    {
        $items = [
            ['id' => '1', 'name' => 'Item 1']
        ];

        $response = new PaginatedResponse(null, $items);

        $this->assertNull($response->getCursor());
        $this->assertEquals($items, $response->getItems());
        $this->assertFalse($response->hasNextPage());
        $this->assertEquals(1, $response->getCount());
        $this->assertFalse($response->isEmpty());
    }

    public function testPaginatedResponseEmpty(): void
    {
        $response = new PaginatedResponse(null, []);

        $this->assertNull($response->getCursor());
        $this->assertEquals([], $response->getItems());
        $this->assertFalse($response->hasNextPage());
        $this->assertEquals(0, $response->getCount());
        $this->assertTrue($response->isEmpty());
    }

    public function testPaginatedResponseFromArray(): void
    {
        $data = [
            'cursor' => 'next-cursor',
            'items' => [
                ['id' => '1', 'name' => 'Item 1'],
                ['id' => '2', 'name' => 'Item 2']
            ]
        ];

        $response = PaginatedResponse::fromArray($data);

        $this->assertEquals('next-cursor', $response->getCursor());
        $this->assertCount(2, $response->getItems());
        $this->assertTrue($response->hasNextPage());
    }

    public function testPaginatedResponseToArray(): void
    {
        $items = [
            ['id' => '1', 'name' => 'Item 1'],
            ['id' => '2', 'name' => 'Item 2']
        ];

        $response = new PaginatedResponse('next-cursor', $items);
        $array = $response->toArray();

        $this->assertEquals('next-cursor', $array['cursor']);
        $this->assertEquals($items, $array['items']);
    }

    public function testPaginatedResponseGetFirstAndLastItem(): void
    {
        $items = [
            ['id' => '1', 'name' => 'Item 1'],
            ['id' => '2', 'name' => 'Item 2'],
            ['id' => '3', 'name' => 'Item 3']
        ];

        $response = new PaginatedResponse('next-cursor', $items);

        $this->assertEquals(['id' => '1', 'name' => 'Item 1'], $response->getFirstItem());
        $this->assertEquals(['id' => '3', 'name' => 'Item 3'], $response->getLastItem());
    }

    public function testPaginatedResponseMap(): void
    {
        $items = [
            ['id' => '1', 'name' => 'Item 1'],
            ['id' => '2', 'name' => 'Item 2']
        ];

        $response = new PaginatedResponse('next-cursor', $items);

        $names = $response->map(
            function ($item) {
                return $item['name'];
            }
        );

        $this->assertEquals(['Item 1', 'Item 2'], $names);
    }

    public function testPaginatedResponseFilter(): void
    {
        $items = [
            ['id' => '1', 'name' => 'Item 1', 'active' => true],
            ['id' => '2', 'name' => 'Item 2', 'active' => false],
            ['id' => '3', 'name' => 'Item 3', 'active' => true]
        ];

        $response = new PaginatedResponse('next-cursor', $items);

        $activeItems = $response->filter(
            function ($item) {
                return $item['active'] === true;
            }
        );

        $this->assertCount(2, $activeItems);
        $activeItems = array_values($activeItems); // Reindex array
        $this->assertEquals('Item 1', $activeItems[0]['name']);
        $this->assertEquals('Item 3', $activeItems[1]['name']);
    }
}
