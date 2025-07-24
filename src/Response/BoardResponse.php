<?php

namespace MondayV2SDK\Response;

use DateTimeImmutable;

/**
 * Response DTO for board data
 *
 * Provides type-safe access to board response data
 * from Monday.com API.
 */
class BoardResponse
{
    private string $id;
    private string $name;
    private ?string $description;
    private string $state;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;
    private string $boardKind;
    private ?string $boardFolderId;
    private array $permissions;
    private array $owner;
    private array $columns;

    public function __construct(
        string $id,
        string $name,
        ?string $description,
        string $state,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        string $boardKind,
        ?string $boardFolderId,
        array $permissions,
        array $owner,
        array $columns
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->state = $state;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->boardKind = $boardKind;
        $this->boardFolderId = $boardFolderId;
        $this->permissions = $permissions;
        $this->owner = $owner;
        $this->columns = $columns;
    }

    /**
     * Get board ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get board name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get board description
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get board state
     *
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Get creation date
     *
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get update date
     *
     * @return DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Get board kind
     *
     * @return string
     */
    public function getBoardKind(): string
    {
        return $this->boardKind;
    }

    /**
     * Get board folder ID
     *
     * @return string|null
     */
    public function getBoardFolderId(): ?string
    {
        return $this->boardFolderId;
    }

    /**
     * Get permissions
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Get owner
     *
     * @return array
     */
    public function getOwner(): array
    {
        return $this->owner;
    }

    /**
     * Get columns
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Create from API response array
     *
     * @param  array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            description: $data['description'] ?? null,
            state: $data['state'] ?? '',
            createdAt: new DateTimeImmutable($data['created_at'] ?? 'now'),
            updatedAt: new DateTimeImmutable($data['updated_at'] ?? 'now'),
            boardKind: $data['board_kind'] ?? '',
            boardFolderId: $data['board_folder_id'] ?? null,
            permissions: $data['permissions'] ?? [],
            owner: $data['owner'] ?? [],
            columns: $data['columns'] ?? []
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
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'state' => $this->state,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'board_kind' => $this->boardKind,
            'board_folder_id' => $this->boardFolderId,
            'permissions' => $this->permissions,
            'owner' => $this->owner,
            'columns' => $this->columns,
        ];
    }

    /**
     * Get column by ID
     *
     * @param  string $columnId
     * @return array<string, mixed>|null
     */
    public function getColumn(string $columnId): ?array
    {
        foreach ($this->columns as $column) {
            if (isset($column['id']) && $column['id'] === $columnId) {
                return $column;
            }
        }

        return null;
    }

    /**
     * Check if board has a specific column
     *
     * @param  string $columnId
     * @return bool
     */
    public function hasColumn(string $columnId): bool
    {
        return $this->getColumn($columnId) !== null;
    }

    /**
     * Get owner name
     *
     * @return string|null
     */
    public function getOwnerName(): ?string
    {
        return $this->owner['name'] ?? null;
    }

    /**
     * Get owner ID
     *
     * @return string|null
     */
    public function getOwnerId(): ?string
    {
        return $this->owner['id'] ?? null;
    }
}
