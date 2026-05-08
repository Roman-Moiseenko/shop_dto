<?php

namespace App\Modules\Content\Domain\ValueObjects;

final class Meta
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = array_merge([
            'title' => '',
            'description' => '',
        ], $data);
    }

    public function getTitle(): string { return $this->data['title'] ?? ''; }
    public function getDescription(): string { return $this->data['description'] ?? ''; }
    public function toArray(): array { return $this->data; }
    public function equals(self $other): bool { return $this->data === $other->data; }

    public static function default(): self { return new self(); }
    public static function fromArray(array $data): self { return new self($data); }
}
