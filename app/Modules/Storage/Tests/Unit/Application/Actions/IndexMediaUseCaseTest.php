<?php

namespace App\Modules\Storage\Tests\Unit\Application\Actions;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Actions\IndexMediaUseCase;
use App\Modules\Storage\Application\DTOs\IndexMediaData;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;

class IndexMediaUseCaseTest extends TestCase
{
    use MockPermission;
    function getModuleName(): string { return  'storage'; }
    function getEntityName(): string { return 'media'; }
    private MediaRepositoryInterface $repo;
    private IndexMediaUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(MediaRepositoryInterface::class);
        $this->useCase = new IndexMediaUseCase($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function returns_media_list_when_permission_granted(): void
    {
        $permission = $this->mockUserPermission(view: true);
        $dto = new IndexMediaData(model_type: 'catalog_product', model_id: 7);

        $expectedMedia = [
            $this->createMediaEntity(1),
            $this->createMediaEntity(2),
        ];

        $this->repo->shouldReceive('listByEntity')
            ->with('catalog_product', 7, null)
            ->once()
            ->andReturn($expectedMedia);

        $result = $this->useCase->execute($dto, $permission);
        $this->assertSame($expectedMedia, $result);
    }

    #[Test]
    public function throws_access_denied_when_missing_view_permission(): void
    {
        $permission = $this->mockUserPermission();
        $dto = new IndexMediaData(model_type: 'catalog_product', model_id: 7);

        $this->repo->shouldNotReceive('listByEntity');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $permission);
    }

    #[Test]
    public function passes_type_filter_to_repository(): void
    {
        $permission = $this->mockUserPermission(view: true);
        $dto = new IndexMediaData(model_type: 'catalog_product', model_id: 7, type: 'gallery');

        $this->repo->shouldReceive('listByEntity')
            ->with('catalog_product', 7, 'gallery')
            ->once()
            ->andReturn([]);

        $result = $this->useCase->execute($dto, $permission);
        $this->assertEmpty($result);
    }

    private function createMediaEntity(int $id): MediaEntity
    {
        $media = new MediaEntity(
            uuid: 'uuid-' . $id,
            modelType: 'catalog_product',
            modelId: 7,
            type: 'image',
            fileName: "photo{$id}.jpg",
            disk: 'public',
            size: 100 * $id,
            mimeType: 'image/jpeg'
        );
        $media->id = $id;
        return $media;
    }
}
