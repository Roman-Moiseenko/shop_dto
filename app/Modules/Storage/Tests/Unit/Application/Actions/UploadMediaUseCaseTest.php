<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions;

use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\UploadMediaUseCase;
use App\Modules\Storage\Application\DTOs\UploadMediaData;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\ImageProcessor;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use Illuminate\Http\UploadedFile;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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

    private MediaRepositoryInterface $repo;
    private ImageProcessor $imageProcessor;
    private UploadMediaUseCase $useCase;
    private FileStorageInterface $fileStorage;
    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(MediaRepositoryInterface::class);
        $this->fileStorage = Mockery::mock(FileStorageInterface::class);
        $this->imageProcessor = Mockery::mock(ImageProcessor::class);
        $this->useCase = new UploadMediaUseCase(
            $this->repo,
            $this->imageProcessor,
            $this->fileStorage,
            'test-disk',
            'test-uploads'
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    #[Test]
    public function uploads_media_successfully(): void
    {
        $permission = $this->mockUserPermission(create: true);
        $file = UploadedFile::fake()->create('test.jpg', 100);
        $dto = new UploadMediaData(
            model_type: 'catalog_product',
            model_id: 1,
            type: 'image',
            file: $file
        );

        $this->fileStorage->shouldReceive('storeUploadedFile')
            ->once()
            ->with(
                Mockery::type(UploadedFile::class),
                'test-uploads/catalog_product/1/',
                Mockery::type('string'),
                'test-disk'
            )
            ->andReturn('stored/test.jpg');

        $this->repo->shouldReceive('save')->once()->andReturnUsing(function (MediaEntity $media) {
            $media->id = 1;
            return $media;
        });
        $this->imageProcessor->shouldReceive('process')->once();

        $media = $this->useCase->execute($dto, $permission);
        $this->assertEquals(1, $media->id);
    }

    #[Test]
    public function throws_access_denied_when_missing_permission(): void
    {
        $permission = $this->mockUserPermission();
        $dto = new UploadMediaData(model_type: 'x', model_id: 1, type: 'image', file: null);

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $permission);
    }
}
