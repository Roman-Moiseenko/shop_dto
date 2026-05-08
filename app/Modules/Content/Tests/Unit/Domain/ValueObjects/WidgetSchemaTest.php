<?php

namespace App\Modules\Content\Tests\Unit\Domain\ValueObjects;

use App\Modules\Content\Domain\ValueObjects\WidgetSchema;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WidgetSchemaTest extends TestCase
{
    private array $validSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validSchema = [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'show_border' => ['type' => 'boolean'],
            ],
        ];
    }

    #[Test] public function creates_from_valid_json_schema(): void
    {
        $schema = new WidgetSchema($this->validSchema);
        $this->assertIsArray($schema->toArray());
        $this->assertArrayHasKey('title', $schema->getProperties());
    }

    #[Test] public function throws_on_invalid_schema(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new WidgetSchema(['type' => 'string']); // не объект
    }

    #[Test] public function throws_when_missing_properties(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new WidgetSchema(['type' => 'object']); // нет properties
    }

    #[Test] public function from_array_static_factory(): void
    {
        $schema = WidgetSchema::fromArray($this->validSchema);
        $this->assertSame($this->validSchema, $schema->toArray());
    }

    #[Test] public function equals_works(): void
    {
        $a = new WidgetSchema($this->validSchema);
        $b = new WidgetSchema($this->validSchema);
        $c = new WidgetSchema(['type' => 'object', 'properties' => ['x' => ['type' => 'number']]]);
        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }
}
