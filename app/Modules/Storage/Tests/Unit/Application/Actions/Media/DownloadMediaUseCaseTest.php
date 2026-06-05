<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Media;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\Media\DownloadMediaUseCase;
use App\Modules\Storage\Application\DTOs\Media\DownloadMediaData;
use App\Modules\Storage\Application\Interfaces\HttpClientInterface;
use App\Modules\Storage\Application\Interfaces\HttpResponseInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class DownloadMediaUseCaseTest extends TestCase
{
    use MockPermission;
    function getModuleName(): string { return  'storage'; }
    function getEntityName(): string { return 'media'; }
    private MediaRepositoryInterface $mediaRepo;
    private MediaFileService $mediaFileService;
    private HttpClientInterface $httpClient;
    private DownloadMediaUseCase $useCase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaRepo = Mockery::mock(MediaRepositoryInterface::class);
        $this->mediaFileService = Mockery::mock(MediaFileService::class);
        $this->httpClient = Mockery::mock(HttpClientInterface::class);
        $this->useCase = new DownloadMediaUseCase(
            $this->mediaRepo,
            $this->mediaFileService,
            $this->httpClient
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    private function createMediaEntity(string $type, string $uuid, string $filename): MediaEntity
    {
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: 'catalog.product',
            modelId: 1,
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
    public function downloads_single_image_replacing_existing(): void
    {
        $dto = new DownloadMediaData(
            model_type: 'catalog.product',
            model_id: 1,
            type: 'image',
            url: 'http://example.com/image.jpg',
            title: 'Downloaded Image'
        );

        // HTTP-ответ
        $response = Mockery::mock(HttpResponseInterface::class);
        $response->shouldReceive('successful')->once()->andReturn(true);
        $response->shouldReceive('body')->once()->andReturn('binary-content');
        $response->shouldReceive('header')->with('Content-Type')->once()->andReturn('image/jpeg');

        $this->httpClient->shouldReceive('get')
            ->with('http://example.com/image.jpg')
            ->once()
            ->andReturn($response);

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

        $this->mediaFileService->shouldReceive('storeOriginalFromContent')
            ->with('binary-content', 'catalog.product', 1, Mockery::type('string'))
            ->once();

        $this->mediaFileService->shouldReceive('getOriginalDisk')
            ->once()
            ->andReturn('local');

        $this->mediaRepo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(MediaEntity::class))
            ->andReturnUsing(function (MediaEntity $media) {
                $media->id = 88;
                return $media;
            });

        $this->mediaFileService->shouldReceive('generateCache')
            ->once()
            ->with(Mockery::type(MediaEntity::class));

        $permission = $this->mockUserPermission(create: true);
        $result = $this->useCase->execute($dto, $permission);

        $this->assertEquals(88, $result->id);
        $this->assertEquals('Downloaded Image', $result->title);
    }

    #[Test]
    public function throws_exception_when_download_fails(): void
    {
        $dto = new DownloadMediaData(
            model_type: 'catalog.product',
            model_id: 1,
            type: 'image',
            url: 'http://example.com/bad.jpg',
        );

        $response = Mockery::mock(HttpResponseInterface::class);
        $response->shouldReceive('successful')->once()->andReturn(false);

        $this->httpClient->shouldReceive('get')
            ->with('http://example.com/bad.jpg')
            ->once()
            ->andReturn($response);

        $permission = $this->mockUserPermission(create: true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Не удалось загрузить файл');
        $this->useCase->execute($dto, $permission);
    }

    #[Test]
    public function throws_access_denied_when_missing_permission(): void
    {
        $dto = new DownloadMediaData(
            model_type: 'catalog.product',
            model_id: 1,
            type: 'image',
            url: 'http://example.com/image.jpg',
        );

        $permission = $this->mockUserPermission(create: false);

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $permission);
    }

}
