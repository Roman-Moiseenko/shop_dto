<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\DownloadMediaUseCase;
use App\Modules\Storage\Application\DTOs\DownloadMediaData;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\HttpClientInterface;
use App\Modules\Storage\Application\Interfaces\HttpResponseInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\ImageProcessor;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Illuminate\Support\Facades\Http;
use Tests\Trait\MockPermission;

class DownloadMediaUseCaseTest extends TestCase
{
    use MockPermission;
    function getModuleName(): string { return  'storage'; }
    function getEntityName(): string { return 'media'; }
    private MediaRepositoryInterface $repo;
    private ImageProcessor $imageProcessor;
    private FileStorageInterface $fileStorage;
    private DownloadMediaUseCase $useCase;
    private HttpClientInterface $httpClient;
    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(MediaRepositoryInterface::class);
        $this->imageProcessor = Mockery::mock(ImageProcessor::class);
        $this->fileStorage = Mockery::mock(FileStorageInterface::class);
        $this->httpClient = Mockery::mock(HttpClientInterface::class);

        $this->useCase = new DownloadMediaUseCase(
            $this->repo,
            $this->imageProcessor,
            $this->fileStorage,
            $this->httpClient,
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
    public function downloads_media_successfully(): void
    {
        $dto = new DownloadMediaData(
            model_type: 'catalog_product',
            model_id: 1,
            type: 'image',
            url: 'http://example.com/image.jpg',
            title: 'Downloaded Image'
        );

        $permission = $this->mockUserPermission(create: true);

        $response = Mockery::mock(HttpResponseInterface::class);
        $response->shouldReceive('successful')->once()->andReturn(true);
        $response->shouldReceive('body')->once()->andReturn('binary-content');
        $response->shouldReceive('header')->with('Content-Type')->once()->andReturn('image/jpeg');

        $this->httpClient->shouldReceive('get')
            ->with('http://example.com/image.jpg')
            ->once()
            ->andReturn($response);

        $this->fileStorage->shouldReceive('put')
            ->once()
            ->with(
                Mockery::on(function ($arg) { return str_starts_with($arg, 'test-uploads/catalog_product/1/'); }),
                'binary-content',
                'test-disk'
            )
            ->andReturn();

        $this->repo->shouldReceive('save')->once()->andReturnUsing(function (MediaEntity $media) {
            $media->id = 5;
            return $media;
        });

        $this->imageProcessor->shouldReceive('process')->once();

        $media = $this->useCase->execute($dto, $permission);
        $this->assertEquals(5, $media->id);
        $this->assertEquals('Downloaded Image', $media->title);
    }

    #[Test]
    public function throws_exception_when_download_fails(): void
    {
        $dto = new DownloadMediaData(
            model_type: 'catalog_product',
            model_id: 1,
            type: 'image',
            url: 'http://example.com/bad.jpg'
        );

        $permission = $this->mockUserPermission(create: true);

        $response = Mockery::mock(HttpResponseInterface::class);
        $response->shouldReceive('successful')->once()->andReturn(false);

        $this->httpClient->shouldReceive('get')
            ->once()
            ->andReturn($response);

        // Ожидаем, что fileStorage->put не вызовется, так как исключение будет раньше
        $this->fileStorage->shouldNotReceive('put');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Не удалось загрузить файл');
        $this->useCase->execute($dto, $permission);
    }

    public function throws_access_denied_when_missing_permission(): void
    {
        $dto = new DownloadMediaData(
            model_type: 'catalog_product',
            model_id: 1,
            type: 'image',
            url: 'http://example.com/image.jpg'
        );

        $permission = $this->mockUserPermission();

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $permission);
    }


}
