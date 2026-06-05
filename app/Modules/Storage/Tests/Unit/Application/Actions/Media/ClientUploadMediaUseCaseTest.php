<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Media;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\Media\ClientUploadMediaUseCase;
use App\Modules\Storage\Application\DTOs\Media\UploadMediaData;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Illuminate\Http\UploadedFile;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class ClientUploadMediaUseCaseTest extends TestCase
{
    use MockPermission;
    function getModuleName(): string { return  'storage'; }
    function getEntityName(): string { return 'media'; }
    private MediaRepositoryInterface $mediaRepo;
    private MediaFileService $mediaFileService;
    private ClientUploadMediaUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->mediaFileService = Mockery::mock(MediaFileService::class);
        $this->useCase = new ClientUploadMediaUseCase($this->mediaRepo, $this->mediaFileService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createFile(): UploadedFile
    {
        return UploadedFile::fake()->create('avatar.jpg', 100);
    }

    private function createMediaEntity(string $type, string $uuid, string $filename, string $modelType = 'auth.client', int $modelId = 42): MediaEntity
    {
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: $modelType,
            modelId: $modelId,
            type: new MediaType($type),
            fileName: $filename,
            disk: 'local',
            size: 100,
            mimeType: 'image/jpeg',
        );
        $media->id = 5;
        return $media;
    }

    #[Test]
    public function uploads_single_image_for_allowed_model_type(): void
    {
        $file = $this->createFile();
        $dto = new UploadMediaData(
            model_type: 'auth.client',
            model_id: 42,
            type: 'image',
            title: 'Аватар',
            file: $file
        );

        // Старое изображение
        $existingMedia = $this->createMediaEntity('image', 'old-uuid', 'old-file.jpg');
        $this->mediaRepo->shouldReceive('findByEntityType')
            ->with('auth.client', 42, 'image')
            ->once()
            ->andReturn($existingMedia);

        $this->mediaFileService->shouldReceive('deleteAllFiles')
            ->with($existingMedia)
            ->once();
        $this->mediaRepo->shouldReceive('delete')
            ->with($existingMedia->id)
            ->once();

        // Сохранение
        $this->mediaFileService->shouldReceive('storeOriginal')
            ->with($file, 'auth.client', 42, Mockery::type('string'))
            ->once();

        $this->mediaFileService->shouldReceive('getOriginalDisk')
            ->once()
            ->andReturn('local');

        $this->mediaRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MediaEntity::class))
            ->andReturnUsing(function (MediaEntity $media) {
                $media->id = 10;
                return $media;
            });

        $this->mediaFileService->shouldReceive('generateCache')
            ->once()
            ->with(Mockery::type(MediaEntity::class));

        $permission = $this->mockUserPermission(create: true);
        $result = $this->useCase->execute($dto, $permission);

        $this->assertEquals(10, $result->id);
        $this->assertEquals('auth.client', $result->modelType);
        $this->assertEquals('Аватар', $result->title);
        $this->assertEquals('image', $result->type->getValue());
    }

    #[Test]
    public function throws_access_denied_for_not_allowed_model_type(): void
    {
        $dto = new UploadMediaData(
            model_type: 'catalog.product', // не разрешено
            model_id: 1,
            type: 'image',
            file: $this->createFile(),
        );

        $permission = $this->mockUserPermission(create: true);

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $permission);
    }

    #[Test]
    public function throws_exception_when_file_not_provided(): void
    {
        $dto = new UploadMediaData(
            model_type: 'auth.client',
            model_id: 1,
            type: 'image',
            file: null
        );

        $permission = $this->mockUserPermission(create: true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Файл обязателен для загрузки');
        $this->useCase->execute($dto, $permission);
    }
}
