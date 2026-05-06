<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\ClientDeleteMediaUseCase;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
class ClientDeleteMediaUseCaseTest extends TestCase
{
    private MediaRepositoryInterface $mediaRepo;
    private FileStorageInterface $fileStorage;
    private ClientDeleteMediaUseCase $useCase;
    private UserPermission $permission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->fileStorage = Mockery::mock(FileStorageInterface::class);
        $this->useCase = new ClientDeleteMediaUseCase($this->mediaRepo, $this->fileStorage);

        // Мок UserPermission не используется внутри UseCase, но требуется по сигнатуре
        $this->permission = Mockery::mock(UserPermission::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function deletes_media_successfully_for_allowed_type(): void
    {
        $uuid = 'test-uuid-123';
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: 'auth.client',
            modelId: 42,
            type: 'image',
            fileName: 'avatar.jpg',
            disk: 'test-disk',
            size: 100,
            mimeType: 'image/jpeg'
        );
        $media->id = 5;

        $this->mediaRepo->shouldReceive('findByUuid')
            ->with($uuid)
            ->once()
            ->andReturn($media);

        $expectedDirectory = dirname($media->getPath());

        $this->fileStorage->shouldReceive('deleteDirectory')
            ->once()
            ->with($expectedDirectory, 'test-disk');

        $this->mediaRepo->shouldReceive('delete')
            ->once()
            ->with(5);

        $this->useCase->execute($uuid, $this->permission);
        // Успех без исключений
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_exception_for_not_allowed_model_type(): void
    {
        $uuid = 'test-uuid-456';
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: 'catalog.product', // не разрешённый тип
            modelId: 1,
            type: 'image',
            fileName: 'product.jpg',
            disk: 'test-disk',
            size: 200,
            mimeType: 'image/jpeg'
        );

        $this->mediaRepo->shouldReceive('findByUuid')
            ->with($uuid)
            ->once()
            ->andReturn($media);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Удаление этого типа медиафайлов недоступно клиенту');

        $this->useCase->execute($uuid, $this->permission);
    }

    #[Test]
    public function throws_exception_when_media_not_found(): void
    {
        $uuid = 'non-existent-uuid';
        $this->mediaRepo->shouldReceive('findByUuid')
            ->with($uuid)
            ->once()
            ->andReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Медиа не найдено');

        $this->useCase->execute($uuid, $this->permission);
    }
}
