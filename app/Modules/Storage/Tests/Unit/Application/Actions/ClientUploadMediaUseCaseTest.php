<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\ClientUploadMediaUseCase;
use App\Modules\Storage\Application\DTOs\UploadMediaData;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\ImageProcessor;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use PHPUnit\Framework\TestCase;
use Mockery;
use Illuminate\Http\UploadedFile;
use Tests\Trait\MockPermission;

class ClientUploadMediaUseCaseTest extends TestCase
{
    use MockPermission;
    function getModuleName(): string { return  'storage'; }
    function getEntityName(): string { return 'media'; }
    private MediaRepositoryInterface $repo;
    private FileStorageInterface $fileStorage;
    private ClientUploadMediaUseCase $useCase;
    private ImageProcessor $imageProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(MediaRepositoryInterface::class);
        $this->fileStorage = Mockery::mock(FileStorageInterface::class);
        $this->imageProcessor = Mockery::mock(ImageProcessor::class);
        $this->useCase = new ClientUploadMediaUseCase(
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

    public function test_uploads_media_for_allowed_model_type(): void
    {
        $file = UploadedFile::fake()->create('avatar.jpg', 100);
        $this->imageProcessor->shouldReceive('process')
            ->once()
            ->with(Mockery::type(MediaEntity::class));

        $dto = new UploadMediaData(
            model_type: 'auth.client',
            model_id: 42,
            type: 'image',
            title: 'Avatar',
            file: $file
        );

        $permission = $this->mockUserPermission(create: true);

        $this->fileStorage->shouldReceive('storeUploadedFile')
            ->once()
            ->with(
                Mockery::type(UploadedFile::class),
                'test-uploads/auth.client/42/',
                Mockery::type('string'),
                'test-disk')
            ->andReturn('stored_filename.jpg');

        $this->repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MediaEntity::class))
            ->andReturnUsing(function (MediaEntity $media) {
                $media->id = 10;
                return $media;
            });

        $media = $this->useCase->execute($dto, $permission);

        $this->assertEquals(10, $media->id);
        $this->assertEquals('auth.client', $media->modelType);
        $this->assertEquals('Avatar', $media->title);
    }

    public function test_throws_access_denied_for_not_allowed_model_type(): void
    {
        $dto = new UploadMediaData(
            model_type: 'catalog.product', // не разрешено для клиента
            model_id: 1,
            type: 'image',
            file: UploadedFile::fake()->create('test.jpg', 100)
        );

        $permission = $this->mockUserPermission(create: true);

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $permission);
    }

    public function test_throws_exception_when_file_not_provided(): void
    {
        $dto = new UploadMediaData(
            model_type: 'auth.client',
            model_id: 1,
            type: 'image',
            file: null // нет файла
        );

        $permission = $this->mockUserPermission(create: true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Файл обязателен для загрузки');
        $this->useCase->execute($dto, $permission);
    }
}
