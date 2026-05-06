<?php

namespace App\Modules\Storage\Domain\Entities;

use DateTimeImmutable;

class MediaEntity
{
    // Property hooks для публичного доступа
    public ?int $id = null {
        get => $this->id;
        set => $this->id = $value;
    }
    public string $uuid {
        get => $this->uuid;
        set => $this->uuid = $value;
    }
    public string $modelType {
        get => $this->modelType;
        set => $this->modelType = $value;
    }
    public int $modelId {
        get => $this->modelId;
        set => $this->modelId = $value;
    }
    public string $type {
        get => $this->type;
        set => $this->type = $value;
    }
    public ?string $title {
        get => $this->title;
        set => $this->title = $value;
    }
    public ?string $description {
        get => $this->description;
        set => $this->description = $value;
    }
    public int $sort {
        get => $this->sort;
        set => $this->sort = $value;
    }
    public string $fileName {
        get => $this->fileName;
        set => $this->fileName = $value;
    }
    public ?string $mimeType {
        get => $this->mimeType;
        set => $this->mimeType = $value;
    }
    public string $disk {
        get => $this->disk;
        set => $this->disk = $value;
    }
    public int $size {
        get => $this->size;
        set => $this->size = $value;
    }
    public array $customProperties {
        get => $this->customProperties;
        set => $this->customProperties = $value;
    }
    public ?DateTimeImmutable $createdAt {
        get => $this->createdAt;
        set => $this->createdAt = $value;
    }
    public ?DateTimeImmutable $updatedAt {
        get => $this->updatedAt;
        set => $this->updatedAt = $value;
    }

    public function __construct(
        string $uuid,
        string $modelType,
        int $modelId,
        string $type,
        string $fileName,
        string $disk,
        int $size,
        ?string $title = null,
        ?string $description = null,
        int $sort = 0,
        ?string $mimeType = null,
        array $customProperties = [],
    ) {
        $this->modelType = $modelType;
        $this->modelId = $modelId;
        $this->type = $type;
        $this->fileName = $fileName;
        $this->disk = $disk;
        $this->size = $size;
        $this->title = $title;
        $this->description = $description;
        $this->sort = $sort;
        $this->mimeType = $mimeType;
        $this->customProperties = $customProperties;
        $this->uuid = $uuid;
    }

    /**
     * Возвращает публичный URL к файлу или его нарезке.
     */
    public function getUrl(string $conversion = ''): string
    {
        $path = $this->getPath($conversion);
        // Предполагаем, что доступ к файловой системе будет предоставлен через сервис
        // В доменной сущности не должно быть прямого обращения к Storage
        // Поэтому этот метод может вернуть относительный путь, а URL сформирует инфраструктурный сервис
        return $path; // или выбросить исключение, если нужен абсолютный URL
    }

    /**
     * Возвращает временную подписанную ссылку (для инфраструктуры).
     */
    public function getTemporaryUrl(\DateTimeInterface $expiration, string $conversion = ''): string
    {
        // Аналогично getUrl, реализация будет в инфраструктуре
        return $this->getPath($conversion);
    }

    /**
     * Формирует путь к файлу относительно корня диска.
     */
    public function getPath(string $conversion = ''): string
    {
        $base = 'uploads/' . $this->modelType . '/' . $this->modelId . '/';
        if ($conversion) {
            $base .= 'cache/' . $conversion . '/';
        }
        $filename = $conversion ? $this->id . '_' . $conversion . '.' . pathinfo($this->fileName, PATHINFO_EXTENSION) : $this->fileName;
        return $base . $filename;
    }

    //TODO Удалить и вынести в DTO
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'model_type' => $this->modelType,
            'model_id' => $this->modelId,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'sort' => $this->sort,
            'file_name' => $this->fileName,
            'mime_type' => $this->mimeType,
            'disk' => $this->disk,
            'size' => $this->size,
            'custom_properties' => $this->customProperties,
            'created_at' => $this->createdAt?->format('c'),
            'updated_at' => $this->updatedAt?->format('c'),
        ];
    }

}
