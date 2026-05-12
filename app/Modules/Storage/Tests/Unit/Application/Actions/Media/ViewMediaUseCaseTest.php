<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions\Media;
use App\Modules\Storage\Application\Actions\Media\ViewMediaUseCase;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use App\Modules\Storage\Infrastructure\Exceptions\MediaFileNotFoundException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ViewMediaUseCaseTest extends TestCase
{
    private MediaRepositoryInterface $repo;
    private ViewMediaUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(MediaRepositoryInterface::class);
        $this->useCase = new ViewMediaUseCase($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_media_by_uuid(): void
    {
        $uuid = 'test-uuid';
        $expectedMedia = new MediaEntity(
            uuid: $uuid,
            modelType: 'catalog_product',
            modelId: 1,
            type: new MediaType('image'),
            fileName: 'test.jpg',
            disk: 'public',
            size: 500,
            mimeType: 'image/jpeg'
        );

        $this->repo->shouldReceive('findByUuid')
            ->with($uuid)
            ->once()
            ->andReturn($expectedMedia);

        $media = $this->useCase->execute($uuid);
        $this->assertSame($expectedMedia, $media);
    }

    #[Test]
    public function throws_exception_when_media_not_found(): void
    {
        $uuid = 'non-existent';
        $this->repo->shouldReceive('findByUuid')
            ->with($uuid)
            ->once()
            ->andReturn(null);

        $this->expectException(MediaFileNotFoundException::class);
        $this->expectExceptionMessage('Медиа не найдено');

        $this->useCase->execute($uuid);
    }
}
