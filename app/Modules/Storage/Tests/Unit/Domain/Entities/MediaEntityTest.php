<?php

namespace App\Modules\Storage\Tests\Unit\Domain\Entities;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use PHPUnit\Framework\TestCase;
use Mockery;
use Illuminate\Support\Str;
class MediaEntityTest extends TestCase
{
    private string $uuid = '550e8400-e29b-41d4-a716-446655440000';

    protected function setUp(): void
    {
        parent::setUp();
        // alias-мок для генерации предсказуемого UUID
        $strMock = Mockery::mock('alias:' . Str::class);
        $strMock->shouldReceive('uuid')
            ->andReturnUsing(function () { return (object)['toString' => $this->uuid]; });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_creates_entity_with_required_fields(): void
    {
        $media = new MediaEntity(
            uuid: $this->uuid,
            modelType: 'catalog_product',
            modelId: 42,
            type: new MediaType('image'),
            fileName: 'photo.jpg',
            disk: 'public',
            size: 1024,
            mimeType: 'image/jpeg'
        );

        $this->assertNull($media->id ?? null);
        // UUID генерируется автоматически, проверяем длину (стандартный UUID 36 символов)
        $this->assertIsString($media->uuid);
        $this->assertSame(36, strlen($media->uuid));
        $this->assertSame('catalog_product', $media->modelType);
        $this->assertSame(42, $media->modelId);
        $this->assertSame('image', $media->type->getValue());
        $this->assertNull($media->title);
        $this->assertNull($media->description);
        $this->assertSame(0, $media->sort);
        $this->assertSame('photo.jpg', $media->fileName);
        $this->assertSame('image/jpeg', $media->mimeType);
        $this->assertSame('public', $media->disk);
        $this->assertSame(1024, $media->size);
    }

    public function test_creates_entity_with_optional_fields(): void
    {
        $media = new MediaEntity(
            uuid: $this->uuid,
            modelType: 'catalog_category',
            modelId: 10,
            type: new MediaType('icon'),
            fileName: 'icon.svg',
            disk: 's3',
            size: 512,
            title: 'Иконка категории',
            description: 'Описание иконки',
            sort: 5,
            mimeType: 'image/svg+xml'
        );

        $this->assertSame('Иконка категории', $media->title);
        $this->assertSame('Описание иконки', $media->description);
        $this->assertSame(5, $media->sort);
    }

    public function test_id_can_be_set(): void
    {
        $media = new MediaEntity(
            uuid: $this->uuid,
            modelType: 'catalog_product',
            modelId: 1,
            type: new MediaType('image'),
            fileName: 'test.png',
            disk: 'public',
            size: 100,
            mimeType: 'image/png'
        );

        $media->id = 77;
        $this->assertSame(77, $media->id);
    }


    public function test_mutable_fields_can_be_updated(): void
    {
        $media = new MediaEntity(
            uuid: $this->uuid,
            modelType: 'catalog_product',
            modelId: 1,
            type: new MediaType('image'),
            fileName: 'test.png',
            disk: 'public',
            size: 100,
            mimeType: 'image/png'
        );

        $media->title = 'Новое название';
        $media->description = 'Новое описание';
        $media->sort = 10;

        $this->assertSame('Новое название', $media->title);
        $this->assertSame('Новое описание', $media->description);
        $this->assertSame(10, $media->sort);
    }

    public function test_get_path_without_conversion(): void
    {
        $media = new MediaEntity(
            uuid: $this->uuid,
            modelType: 'catalog_product',
            modelId: 42,
            type: new MediaType('image'),
            fileName: 'photo.jpg',
            disk: 'public',
            size: 1024,
            mimeType: 'image/jpeg'
        );
        $media->id = 1;

        $expected = 'uploads/catalog_product/42/photo.jpg';
        $this->assertSame($expected, $media->getPath());
    }

    public function test_get_path_with_conversion(): void
    {
        $media = new MediaEntity(
            uuid: $this->uuid,
            modelType: 'catalog_product',
            modelId: 42,
            type: new MediaType('image'),
            fileName: 'photo.jpg',
            disk: 'public',
            size: 1024,
            mimeType: 'image/jpeg'
        );
        $media->id = 1;

        $expected = 'uploads/catalog_product/42/cache/card/1_card.jpg';
        $this->assertSame($expected, $media->getPath('card'));
    }
}
