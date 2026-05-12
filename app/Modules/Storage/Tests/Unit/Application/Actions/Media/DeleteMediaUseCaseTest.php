<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Media;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\Media\DeleteMediaUseCase;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class DeleteMediaUseCaseTest extends TestCase
{
    use MockPermission;
    function getModuleName(): string { return  'storage'; }
    function getEntityName(): string { return 'media'; }
    private MediaRepositoryInterface $mediaRepo;
    private MediaFileService $mediaFileService;
    private DeleteMediaUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->mediaFileService = Mockery::mock(MediaFileService::class);
        $this->useCase = new DeleteMediaUseCase($this->mediaRepo, $this->mediaFileService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMediaEntity(string $type = 'image', string $uuid = 'test-uuid'): MediaEntity
    {
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: 'catalog.product',
            modelId: 1,
            type: new MediaType($type),
            fileName: 'photo.jpg',
            disk: 'local',
            size: 100,
            mimeType: 'image/jpeg'
        );
        $media->id = 42;
        return $media;
    }

    #[Test]
    public function deletes_media_and_files_successfully(): void
    {
        $media = $this->createMediaEntity();

        $this->mediaRepo->shouldReceive('findById')
            ->with(42)
            ->once()
            ->andReturn($media);

        $this->mediaFileService->shouldReceive('deleteAllFiles')
            ->with($media)
            ->once();

        $this->mediaRepo->shouldReceive('delete')
            ->with(42)
            ->once();

        $permission = $this->mockUserPermission(delete: true);
        $this->useCase->execute(42, $permission);
        // Если исключения нет – успех
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_missing_permission(): void
    {
        $permission = $this->mockUserPermission(delete: false);

        $this->mediaRepo->shouldNotReceive('findById');
        $this->mediaFileService->shouldNotReceive('deleteAllFiles');
        $this->mediaRepo->shouldNotReceive('delete');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute(1, $permission);
    }

    #[Test]
    public function throws_exception_when_media_not_found(): void
    {
        $this->mediaRepo->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $permission = $this->mockUserPermission(delete: true);

        $this->mediaFileService->shouldNotReceive('deleteAllFiles');
        $this->mediaRepo->shouldNotReceive('delete');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Медиа не найдено');
        $this->useCase->execute(999, $permission);
    }
}
