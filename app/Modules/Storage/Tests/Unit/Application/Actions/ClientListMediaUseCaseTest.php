<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\ClientListMediaUseCase;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class ClientListMediaUseCaseTest extends TestCase
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
    private ClientListMediaUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->mediaFileService = Mockery::mock(MediaFileService::class);
        $this->useCase = new ClientListMediaUseCase($this->mediaRepo, $this->mediaFileService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMediaEntity(string $type, string $uuid, string $title = 'test'): MediaEntity
    {
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: 'auth_client',
            modelId: 42,
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

    #[Test]
    public function returns_media_for_client_when_ids_match(): void
    {
        $media = $this->createMediaEntity('image', 'uuid-image', 'Аватар');
        $this->mediaRepo->shouldReceive('listByEntity')
            ->with('auth.client', 42)
            ->once()
            ->andReturn([$media]);

        $this->mediaFileService->shouldReceive('ensureCacheExists')->with($media)->once();
        $this->mediaFileService->shouldReceive('getThumbsConfig')->with($media)->andReturn([]);
        $this->mediaFileService->shouldReceive('getCacheFullUrl')->with($media)->andReturn('/cache/photo.jpg');

        $permission = $this->mockUserPermission(id: 42);

        $result = $this->useCase->execute('auth.client', 42, $permission);

        $this->assertArrayHasKey('image', $result);
        $this->assertSame('uuid-image', $result['image']['uuid']);
        $this->assertSame('/cache/photo.jpg', $result['image']['url']);
    }

    #[Test]
    public function returns_media_for_manager_with_view_permission(): void
    {
        $media = $this->createMediaEntity('image', 'uuid-image', 'Аватар');
        $this->mediaRepo->shouldReceive('listByEntity')
            ->with('auth.client', 42)
            ->once()
            ->andReturn([$media]);

        $this->mediaFileService->shouldReceive('ensureCacheExists')->with($media)->once();
        $this->mediaFileService->shouldReceive('getThumbsConfig')->with($media)->andReturn([]);
        $this->mediaFileService->shouldReceive('getCacheFullUrl')->with($media)->andReturn('/cache/photo.jpg');

        $permission = $this->mockUserPermission(view: true, id: 1); // менеджер с правом просмотра

        $result = $this->useCase->execute('auth.client', 42, $permission);

        $this->assertArrayHasKey('image', $result);
    }

    #[Test]
    public function throws_access_denied_for_not_allowed_model_type(): void
    {
        $permission = $this->mockUserPermission(view: true, id: 1);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Недопустимый тип сущности');

        $this->useCase->execute('catalog.product', 1, $permission);
    }

    #[Test]
    public function groups_gallery_into_array(): void
    {
        $gallery1 = $this->createMediaEntity('gallery', 'uuid-g1', 'Gallery 1');
        $gallery2 = $this->createMediaEntity('gallery', 'uuid-g2', 'Gallery 2');

        $this->mediaRepo->shouldReceive('listByEntity')
            ->with('auth.client', 42)
            ->once()
            ->andReturn([$gallery1, $gallery2]);

        foreach ([$gallery1, $gallery2] as $media) {
            $this->mediaFileService->shouldReceive('ensureCacheExists')->with($media)->once();
            $this->mediaFileService->shouldReceive('getThumbsConfig')->with($media)->andReturn([]);
            $this->mediaFileService->shouldReceive('getCacheFullUrl')->with($media)->andReturn('/cache/' . $media->fileName);
        }

        $permission = $this->mockUserPermission(view: false, id: 42);

        $result = $this->useCase->execute('auth.client', 42, $permission);

        $this->assertArrayHasKey('gallery', $result);
        $this->assertCount(2, $result['gallery']);
        $this->assertSame('uuid-g1', $result['gallery'][0]['uuid']);
    }
}
