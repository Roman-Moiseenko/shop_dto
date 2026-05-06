<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\DeleteMediaUseCase;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class DeleteMediaUseCaseTest extends TestCase
{
    use MockPermission;
    function getModuleName(): string { return  'storage'; }
    function getEntityName(): string { return 'media'; }
    private MediaRepositoryInterface $mediaRepo;
    private FileStorageInterface $fileStorage;
    private DeleteMediaUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->fileStorage = Mockery::mock(FileStorageInterface::class);
        $this->useCase = new DeleteMediaUseCase($this->mediaRepo, $this->fileStorage);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function deletes_media_and_files_successfully(): void
    {
        $media = new MediaEntity(
            uuid: 'test-uuid',
            modelType: 'catalog_product',
            modelId: 1,
            type: new MediaType('image'),
            fileName: 'photo.jpg',
            disk: 'test-disk',
            size: 100,
            mimeType: 'image/jpeg'
        );
        $media->id = 42;

        $this->mediaRepo->shouldReceive('findById')
            ->with(42)
            ->once()
            ->andReturn($media);

        $expectedDirectory = dirname($media->getPath()); // вызываем getPath() для получения пути

        $this->fileStorage->shouldReceive('deleteDirectory')
            ->once()
            ->with($expectedDirectory, 'test-disk');

        $this->mediaRepo->shouldReceive('delete')
            ->once()
            ->with(42);

        $permission = $this->mockUserPermission(delete: true);
        $this->useCase->execute(42, $permission);
        // Если исключения нет – успех
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_missing_permission(): void
    {
        $permission = $this->mockUserPermission();

        $this->mediaRepo->shouldNotReceive('findById');
        $this->fileStorage->shouldNotReceive('deleteDirectory');
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

        $this->fileStorage->shouldNotReceive('deleteDirectory');
        $this->mediaRepo->shouldNotReceive('delete');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Медиа не найдено');
        $this->useCase->execute(999, $permission);
    }
}
