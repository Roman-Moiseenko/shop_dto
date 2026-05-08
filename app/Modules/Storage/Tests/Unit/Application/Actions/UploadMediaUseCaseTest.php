<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions;

use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\UploadMediaUseCase;
use App\Modules\Storage\Application\DTOs\UploadMediaData;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\ImageProcessor;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Illuminate\Http\UploadedFile;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Nonstandard\Uuid;
use Tests\Trait\MockPermission;

class UploadMediaUseCaseTest extends TestCase
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
    private UploadMediaUseCase $useCase;
    private string $testUuid = '550e8400-e29b-41d4-a716-446655440000';

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->mediaFileService = Mockery::mock(MediaFileService::class);
        $this->useCase = new UploadMediaUseCase($this->mediaRepo, $this->mediaFileService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createFile(): UploadedFile
    {
        return UploadedFile::fake()->create('test.jpg', 100);
    }

    private function createMediaEntity(string $type, string $uuid, string $filename): MediaEntity
    {
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: 'catalog.product',
            modelId: 1,
            type: new MediaType($type),
            fileName: $filename,
            mimeType: 'image/jpeg',
            disk: 'local',
            size: 100,
        );
        $media->id = 5;
        return $media;
    }

    #[Test]
    public function uploads_single_image_replacing_existing(): void
    {
        $file = $this->createFile();
        $dto = new UploadMediaData(
            model_type: 'catalog.product',
            model_id: 1,
            type: 'image',
            file: $file,
            title: 'Test Image'
        );

        // Старое изображение для одиночного типа
        $existingMedia = $this->createMediaEntity('image', 'old-uuid', 'old-file.jpg');
        $this->mediaRepo->shouldReceive('findByEntityType')
            ->with('catalog.product', 1, 'image')
            ->once()
            ->andReturn($existingMedia);

        $this->mediaFileService->shouldReceive('deleteAllFiles')
            ->with($existingMedia)
            ->once();
        $this->mediaRepo->shouldReceive('delete')
            ->with($existingMedia->id)
            ->once();

        $this->mediaFileService->shouldReceive('storeOriginal')
            ->with($file, 'catalog.product', 1, Mockery::type('string'))
            ->once();

        $this->mediaFileService->shouldReceive('getOriginalDisk')
            ->once()
            ->andReturn('local');

        $this->mediaRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MediaEntity::class))
            ->andReturnUsing(function (MediaEntity $media) {
                $media->id = 42;
                return $media;
            });

        $this->mediaFileService->shouldReceive('generateCache')
            ->once()
            ->with(Mockery::type(MediaEntity::class));

        $permission = $this->mockUserPermission(create: true);
        $result = $this->useCase->execute($dto, $permission);

        $this->assertEquals(42, $result->id);
        $this->assertEquals('Test Image', $result->title);
        $this->assertEquals('image', $result->type->getValue());
    }

    #[Test]
    public function uploads_gallery_image_without_replacing(): void
    {
        $file = $this->createFile();
        $dto = new UploadMediaData(
            model_type: 'catalog.product',
            model_id: 1,
            type: 'gallery',
            file: $file,
        );

        $this->mediaRepo->shouldNotReceive('findByEntityType');
        $this->mediaFileService->shouldNotReceive('deleteAllFiles');
        $this->mediaRepo->shouldNotReceive('delete');

        $this->mediaFileService->shouldReceive('storeOriginal')
            ->with($file, 'catalog.product', 1, Mockery::type('string'))
            ->once();

        $this->mediaFileService->shouldReceive('getOriginalDisk')
            ->once()
            ->andReturn('local');

        $this->mediaRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MediaEntity::class))
            ->andReturnUsing(function (MediaEntity $media) {
                $media->id = 99;
                return $media;
            });

        $this->mediaFileService->shouldReceive('generateCache')
            ->once()
            ->with(Mockery::type(MediaEntity::class));

        $permission = $this->mockUserPermission(create: true);
        $result = $this->useCase->execute($dto, $permission);

        $this->assertEquals(99, $result->id);
        $this->assertEquals('gallery', $result->type->getValue());
    }

    #[Test]
    public function throws_access_denied_when_missing_permission(): void
    {
        $dto = new UploadMediaData(
            model_type: 'catalog.product',
            model_id: 1,
            type: 'image',
            file: $this->createFile(),
        );

        $permission = $this->mockUserPermission(create: false);

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $permission);
    }
}
