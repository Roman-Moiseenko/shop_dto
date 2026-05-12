<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Media;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\Media\ClearCacheUseCase;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class ClearCacheUseCaseTest extends TestCase
{
    use MockPermission;

    function getModuleName(): string
    {
        return 'storage';
    }

    function getEntityName(): string
    {
        return 'media';
    }
    private MediaRepositoryInterface $mediaRepo;
    private MediaFileService $mediaFileService;
    private ClearCacheUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->mediaFileService = Mockery::mock(MediaFileService::class);
        $this->useCase = new ClearCacheUseCase($this->mediaRepo, $this->mediaFileService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMediaEntity(string $uuid): MediaEntity
    {
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: 'catalog.product',
            modelId: 1,
            type: new MediaType('image'),
            fileName: 'photo.jpg',
            disk: 'local',
            size: 100,
            mimeType: 'image/jpeg',
        );
        $media->id = rand(1, 1000);
        return $media;
    }

    #[Test]
    public function clears_cache_for_all_media(): void
    {
        $media1 = $this->createMediaEntity('uuid-1');
        $media2 = $this->createMediaEntity('uuid-2');

        $this->mediaRepo->shouldReceive('listAll')
            ->with(null, null)
            ->once()
            ->andReturn([$media1, $media2]);

        $this->mediaFileService->shouldReceive('deleteCache')
            ->with($media1)
            ->once();
        $this->mediaFileService->shouldReceive('deleteCache')
            ->with($media2)
            ->once();

        $permission = $this->mockUserPermission(delete: true);
        $count = $this->useCase->execute($permission, null, null);

        $this->assertEquals(2, $count);
    }

    #[Test]
    public function clears_cache_for_specific_entity(): void
    {
        $media = $this->createMediaEntity('uuid-1');

        $this->mediaRepo->shouldReceive('listAll')
            ->with('catalog.product', 1)
            ->once()
            ->andReturn([$media]);

        $this->mediaFileService->shouldReceive('deleteCache')
            ->with($media)
            ->once();

        $permission = $this->mockUserPermission(delete: true);
        $count = $this->useCase->execute($permission, 'catalog.product', 1);

        $this->assertEquals(1, $count);
    }

    #[Test]
    public function returns_zero_when_no_media_found(): void
    {
        $this->mediaRepo->shouldReceive('listAll')
            ->with('catalog.product', 999)
            ->once()
            ->andReturn([]);

        $permission = $this->mockUserPermission(delete: true);
        $count = $this->useCase->execute($permission, 'catalog.product', 999);

        $this->assertEquals(0, $count);
    }

    #[Test]
    public function throws_access_denied_when_missing_permission(): void
    {
        $permission = $this->mockUserPermission(delete: false);

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($permission, null, null);
    }
}
