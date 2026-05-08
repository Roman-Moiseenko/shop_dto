<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\ClientDeleteMediaUseCase;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use App\Modules\Storage\Infrastructure\Exceptions\MediaFileNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
class ClientDeleteMediaUseCaseTest extends TestCase
{
    private MediaRepositoryInterface $mediaRepo;
    private MediaFileService $mediaFileService;
    private ClientDeleteMediaUseCase $useCase;
    private UserPermission $permission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->mediaFileService = Mockery::mock(MediaFileService::class);
        $this->useCase = new ClientDeleteMediaUseCase($this->mediaRepo, $this->mediaFileService);

        // Мок UserPermission не используется внутри UseCase, но требуется по сигнатуре
        $this->permission = Mockery::mock(UserPermission::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMediaEntity(string $modelType, string $uuid, string $type = 'image'): MediaEntity
    {
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: $modelType,
            modelId: 42,
            type: new MediaType($type),
            fileName: 'file.jpg',
            disk: 'local',
            size: 100,
            mimeType: 'image/jpeg',
        );
        $media->id = 5;
        return $media;
    }

    #[Test]
    public function deletes_media_successfully_for_allowed_type(): void
    {
        $uuid = 'test-uuid-123';
        $media = $this->createMediaEntity('auth.client', $uuid);

        $this->mediaRepo->shouldReceive('findByUuid')
            ->with($uuid)
            ->once()
            ->andReturn($media);

        $this->mediaFileService->shouldReceive('deleteAllFiles')
            ->once()
            ->with($media);

        $this->mediaRepo->shouldReceive('delete')
            ->once()
            ->with($media->id);

        $this->useCase->execute($uuid, $this->permission);
        // Успех без исключений
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_exception_for_not_allowed_model_type(): void
    {
        $uuid = 'test-uuid-456';
        $media = $this->createMediaEntity('catalog.product', $uuid);

        $this->mediaRepo->shouldReceive('findByUuid')
            ->with($uuid)
            ->once()
            ->andReturn($media);

        $this->expectException(\InvalidArgumentException::class);
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

        $this->expectException(MediaFileNotFoundException::class);
        $this->expectExceptionMessage('Медиа не найдено');

        $this->useCase->execute($uuid, $this->permission);
    }
}
