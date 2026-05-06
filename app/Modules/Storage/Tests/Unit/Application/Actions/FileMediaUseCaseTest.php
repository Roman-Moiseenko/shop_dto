<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\FileMediaUseCase;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use App\Modules\Storage\Infrastructure\Exceptions\MediaFileNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class FileMediaUseCaseTest extends TestCase
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
    private FileStorageInterface $fileStorage;
    private FileMediaUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->fileStorage = Mockery::mock(FileStorageInterface::class);
        $this->useCase = new FileMediaUseCase($this->mediaRepo, $this->fileStorage);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_full_path_when_permission_granted_and_file_exists(): void
    {
        $uuid = 'test-uuid';
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: 'catalog_product',
            modelId: 1,
            type: new MediaType('image'),
            fileName: 'photo.jpg',
            disk: 'public',
            size: 100,
            mimeType: 'image/jpeg'
        );

        $this->mediaRepo->shouldReceive('findByUuid')
            ->with($uuid)
            ->once()
            ->andReturn($media);

        $expectedRelativePath = $media->getPath();
        $expectedAbsolutePath = '/var/www/public/storage/' . $expectedRelativePath;

        $this->fileStorage->shouldReceive('exists')
            ->with($expectedRelativePath, 'public')
            ->once()
            ->andReturn(true);

        $this->fileStorage->shouldReceive('fullPath')
            ->with($expectedRelativePath, 'public')
            ->once()
            ->andReturn($expectedAbsolutePath);

        $permission = $this->mockUserPermission(view: true);
        $result = $this->useCase->execute($uuid, $permission);

        $this->assertSame($expectedAbsolutePath, $result);
    }

    #[Test]
    public function throws_access_denied_when_missing_view_permission(): void
    {
        $permission = $this->mockUserPermission(view: false);

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute('any-uuid', $permission);
    }

    #[Test]
    public function throws_exception_when_media_not_found(): void
    {
        $uuid = 'non-existent';
        $this->mediaRepo->shouldReceive('findByUuid')
            ->with($uuid)
            ->once()
            ->andReturn(null);

        $permission = $this->mockUserPermission(view: true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Медиа не найдено');
        $this->useCase->execute($uuid, $permission);
    }

    #[Test]
    public function throws_file_not_found_when_file_does_not_exist_on_disk(): void
    {
        $uuid = 'test-uuid';
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: 'catalog_product',
            modelId: 1,
            type: new MediaType('image'),
            fileName: 'photo.jpg',
            disk: 'public',
            size: 100,
            mimeType: 'image/jpeg'
        );

        $this->mediaRepo->shouldReceive('findByUuid')
            ->with($uuid)
            ->once()
            ->andReturn($media);

        $relativePath = $media->getPath();

        $this->fileStorage->shouldReceive('exists')
            ->with($relativePath, 'public')
            ->once()
            ->andReturn(false);

        $permission = $this->mockUserPermission(view: true);

        $this->expectException(MediaFileNotFoundException::class);
        $this->useCase->execute($uuid, $permission);
    }


}
