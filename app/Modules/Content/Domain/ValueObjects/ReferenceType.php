<?php

namespace App\Modules\Content\Domain\ValueObjects;

use InvalidArgumentException;

final class ReferenceType
{
    public const string PAGE = 'page';
    public const string BLOG_CATEGORY = 'blog.category';
    public const string BLOG_POST = 'blog.post';
    public const string CATALOG_PRODUCT = 'catalog.product';
    public const string CATALOG_CATEGORY = 'catalog.category';
    public const string CUSTOM = 'custom';
    public const string CONTENT_PAGE = 'content.page';
    public const string CONTENT_POST = 'content.post';

    private const array ALLOWED = [
        self::PAGE,
        self::BLOG_CATEGORY,
        self::BLOG_POST,
        self::CATALOG_PRODUCT,
        self::CATALOG_CATEGORY,
        self::CUSTOM,
        self::CONTENT_PAGE,
        self::CONTENT_POST,
    ];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidArgumentException("Недопустимый тип ссылки: {$value}");
        }
        $this->value = $normalized;
    }

    public function getValue(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    public static function page(): self { return new self(self::PAGE); }
    public static function blogCategory(): self { return new self(self::BLOG_CATEGORY); }
    public static function custom(): self { return new self(self::CUSTOM); }
}
