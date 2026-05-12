<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Media;
use App\Modules\Storage\Application\Actions\Media\PublicListMediaUseCase;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PublicListMediaUseCaseTest extends TestCase
{
    private MediaRepositoryInterface $mediaRepo;
    private MediaFileService $mediaFileService;
    private PublicListMediaUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->mediaFileService = Mockery::mock(MediaFileService::class);
        $this->useCase = new PublicListMediaUseCase($this->mediaRepo, $this->mediaFileService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** Создать медиа-сущность с заданным типом и uuid */
    private function createMediaEntity(string $type, string $uuid, string $title = 'test'): MediaEntity
    {
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: 'catalog_product',
            modelId: 1,
            type: new MediaType($type),
            fileName: 'photo.jpg',
            disk: 'local',
            size: 100,
            title: $title,
            mimeType: 'image/jpeg',
        );
        $media->id = rand(1, 1000);
        return $media;
    }

    /** Настроить мок файлового сервиса для указанного медиа */
    private function mockMediaFileServiceForMedia(MediaEntity $media): void
    {
        $this->mediaFileService->shouldReceive('ensureCacheExists')->with($media)->once();
        $this->mediaFileService->shouldReceive('getThumbsConfig')->with($media)->andReturn([]);
        $this->mediaFileService->shouldReceive('getCacheFullUrl')->with($media)->andReturn('/cache/' . $media->fileName);
    }

    #[Test]
    public function returns_grouped_media_for_all_types(): void
    {
        $image = $this->createMediaEntity('image', 'uuid-image', 'Product Image');
        $icon = $this->createMediaEntity('icon', 'uuid-icon', 'Product Icon');
        $gallery1 = $this->createMediaEntity('gallery', 'uuid-gallery1', 'Gallery 1');
        $gallery2 = $this->createMediaEntity('gallery', 'uuid-gallery2', 'Gallery 2');

        $mediaList = [$image, $icon, $gallery1, $gallery2];

        $this->mediaRepo->shouldReceive('listByEntity')
            ->with('catalog.product', 1, null)
            ->once()
            ->andReturn($mediaList);

        // Настроим ожидания для каждого медиа
        foreach ($mediaList as $media) {
            $this->mockMediaFileServiceForMedia($media);
        }

        $result = $this->useCase->execute('catalog.product', 1, null);

        // Проверяем структуру
        $this->assertArrayHasKey('image', $result);
        $this->assertArrayHasKey('icon', $result);
        $this->assertArrayHasKey('gallery', $result);

        // image – одиночный объект
        $this->assertIsArray($result['image']);
        $this->assertSame('uuid-image', $result['image']['uuid']);
        $this->assertSame('Product Image', $result['image']['title']);

        // icon – одиночный объект
        $this->assertIsArray($result['icon']);
        $this->assertSame('uuid-icon', $result['icon']['uuid']);

        // gallery – массив
        $this->assertCount(2, $result['gallery']);
        $this->assertSame('uuid-gallery1', $result['gallery'][0]['uuid']);
        $this->assertSame('uuid-gallery2', $result['gallery'][1]['uuid']);
    }

    #[Test]
    public function returns_only_requested_type_when_type_is_specified(): void
    {
        $image = $this->createMediaEntity('image', 'uuid-image');

        $this->mediaRepo->shouldReceive('listByEntity')
            ->with('catalog.product', 1, 'image')
            ->once()
            ->andReturn([$image]);

        $this->mockMediaFileServiceForMedia($image);

        $result = $this->useCase->execute('catalog.product', 1, 'image');

        $this->assertArrayHasKey('image', $result);
        $this->assertCount(1, $result);
        $this->assertSame('uuid-image', $result['image']['uuid']);
    }

    #[Test]
    public function returns_null_for_single_type_when_no_media(): void
    {
        $this->mediaRepo->shouldReceive('listByEntity')
            ->with('catalog.product', 1, 'icon')
            ->once()
            ->andReturn([]);

        $result = $this->useCase->execute('catalog.product', 1, 'icon');

        $this->assertArrayHasKey('icon', $result);
        $this->assertNull($result['icon']);
    }

    #[Test]
    public function returns_empty_array_for_gallery_when_no_media(): void
    {
        $this->mediaRepo->shouldReceive('listByEntity')
            ->with('catalog.product', 1, 'gallery')
            ->once()
            ->andReturn([]);

        $result = $this->useCase->execute('catalog.product', 1, 'gallery');

        $this->assertArrayHasKey('gallery', $result);
        $this->assertSame([], $result['gallery']);
    }

    #[Test]
    public function does_not_include_empty_groups_when_type_not_specified(): void
    {
        // Только галерея без image
        $gallery = $this->createMediaEntity('gallery', 'uuid-gallery');

        $this->mediaRepo->shouldReceive('listByEntity')
            ->with('catalog.product', 1, null)
            ->once()
            ->andReturn([$gallery]);

        $this->mockMediaFileServiceForMedia($gallery);

        $result = $this->useCase->execute('catalog.product', 1, null);

        $this->assertArrayHasKey('gallery', $result);
        $this->assertArrayNotHasKey('image', $result);
        $this->assertArrayNotHasKey('icon', $result);
    }

    #[Test]
    public function includes_thumbnails_when_available(): void
    {
        $image = $this->createMediaEntity('image', 'uuid-image');
        $this->mediaRepo->shouldReceive('listByEntity')->andReturn([$image]);

        // Настройка кэша: ensureCacheExists, getThumbsConfig, thumbExists, getThumbRelativeUrl
        $this->mediaFileService->shouldReceive('ensureCacheExists')->with($image)->once();
        $this->mediaFileService->shouldReceive('getThumbsConfig')->with($image)->andReturn([
            'thumb' => ['width' => 150, 'height' => 150],
            'card' => ['width' => 600, 'height' => 600],
        ]);
        $this->mediaFileService->shouldReceive('thumbExists')->with($image, 'thumb')->andReturn(true);
        $this->mediaFileService->shouldReceive('thumbExists')->with($image, 'card')->andReturn(false);
        $this->mediaFileService->shouldReceive('getThumbUrl')->with($image, 'thumb')->andReturn('/cache/thumb/image.jpg');
        $this->mediaFileService->shouldReceive('getCacheFullUrl')->with($image)->andReturn('/cache/image.jpg');

        $result = $this->useCase->execute('catalog.product', 1, null);

        $this->assertArrayHasKey('thumbnails', $result['image']);
        $this->assertArrayHasKey('thumb', $result['image']['thumbnails']);
        $this->assertArrayNotHasKey('card', $result['image']['thumbnails']);
        $this->assertSame('/cache/thumb/image.jpg', $result['image']['thumbnails']['thumb']);
    }
}
